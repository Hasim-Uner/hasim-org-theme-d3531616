#!/usr/bin/env python3
"""Generate an MP3 from essay text with Google Cloud Text-to-Speech."""

from __future__ import annotations

import argparse
import base64
import json
import os
import re
import sys
import time
from pathlib import Path
from typing import Iterable
from urllib import error, parse, request


DEFAULT_ENV_FILE = Path.home() / ".config" / "hasim-org" / "google-tts.env"
DEFAULT_VOICE = "de-DE-Neural2-B"
DEFAULT_LANGUAGE = "de-DE"
MAX_REQUEST_BYTES = 4800


def load_env_file(path: Path) -> None:
    if not path.exists():
        return

    for raw_line in path.read_text(encoding="utf-8").splitlines():
        line = raw_line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue

        key, value = line.split("=", 1)
        key = key.strip()
        value = value.strip().strip("'\"“”‘’")

        if key and key not in os.environ:
            os.environ[key] = value


def read_text(args: argparse.Namespace) -> str:
    if args.text:
        return args.text.strip()

    if not args.input:
        raise SystemExit("Bitte --input DATEI oder --text TEXT angeben.")

    input_path = Path(args.input)
    if not input_path.exists():
        raise SystemExit(f"Eingabedatei nicht gefunden: {input_path}")

    return input_path.read_text(encoding=args.encoding).strip()


def default_output_path(args: argparse.Namespace) -> Path:
    if args.output:
        return Path(args.output)

    if args.input:
        slug = Path(args.input).stem
    else:
        slug = "essay-audio"

    return Path("generated-audio") / f"{slug}.mp3"


def split_text(text: str, max_bytes: int = MAX_REQUEST_BYTES) -> list[str]:
    normalized = re.sub(r"\s+", " ", text).strip()
    if not normalized:
        raise SystemExit("Der Text ist leer.")

    sentences = re.split(r"(?<=[.!?])\s+", normalized)
    chunks: list[str] = []
    current = ""

    for sentence in sentences:
        sentence = sentence.strip()
        if not sentence:
            continue

        candidate = f"{current} {sentence}".strip()
        if len(candidate.encode("utf-8")) <= max_bytes:
            current = candidate
            continue

        if current:
            chunks.append(current)
            current = ""

        if len(sentence.encode("utf-8")) <= max_bytes:
            current = sentence
            continue

        chunks.extend(split_oversized_sentence(sentence, max_bytes))

    if current:
        chunks.append(current)

    return chunks


def split_oversized_sentence(sentence: str, max_bytes: int) -> list[str]:
    words = sentence.split()
    chunks: list[str] = []
    current = ""

    for word in words:
        candidate = f"{current} {word}".strip()
        if len(candidate.encode("utf-8")) <= max_bytes:
            current = candidate
            continue

        if current:
            chunks.append(current)
            current = ""

        if len(word.encode("utf-8")) > max_bytes:
            raise SystemExit(f"Ein einzelnes Wort ist zu lang fuer Google TTS: {word[:40]}...")

        current = word

    if current:
        chunks.append(current)

    return chunks


def synthesize_chunk(
    api_key: str,
    text: str,
    voice: str,
    language_code: str,
    speaking_rate: float,
    pitch: float,
) -> bytes:
    endpoint = "https://texttospeech.googleapis.com/v1/text:synthesize"
    url = f"{endpoint}?{parse.urlencode({'key': api_key})}"
    payload = {
        "input": {"text": text},
        "voice": {
            "languageCode": language_code,
            "name": voice,
        },
        "audioConfig": {
            "audioEncoding": "MP3",
            "speakingRate": speaking_rate,
            "pitch": pitch,
        },
    }
    data = json.dumps(payload).encode("utf-8")
    req = request.Request(
        url,
        data=data,
        headers={"Content-Type": "application/json; charset=utf-8"},
        method="POST",
    )

    try:
        with request.urlopen(req, timeout=60) as response:
            response_data = json.loads(response.read().decode("utf-8"))
    except error.HTTPError as exc:
        detail = exc.read().decode("utf-8", errors="replace")
        raise SystemExit(f"Google TTS Fehler {exc.code}: {detail}") from exc
    except error.URLError as exc:
        raise SystemExit(f"Google TTS konnte nicht erreicht werden: {exc}") from exc

    audio_content = response_data.get("audioContent")
    if not audio_content:
        raise SystemExit("Google TTS Antwort enthielt kein audioContent.")

    return base64.b64decode(audio_content)


def write_mp3(output_path: Path, audio_parts: Iterable[bytes]) -> None:
    output_path.parent.mkdir(parents=True, exist_ok=True)
    with output_path.open("wb") as handle:
        for audio_part in audio_parts:
            handle.write(audio_part)


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Erzeugt eine MP3-Datei aus Essay-Text via Google Cloud Text-to-Speech."
    )
    parser.add_argument("--input", "-i", help="Textdatei, die vorgelesen werden soll.")
    parser.add_argument("--text", help="Kurzer Text direkt aus der Kommandozeile.")
    parser.add_argument("--output", "-o", help="Zielpfad fuer die MP3-Datei.")
    parser.add_argument("--voice", default=DEFAULT_VOICE, help=f"Google-Stimme, Standard: {DEFAULT_VOICE}")
    parser.add_argument("--language-code", default=DEFAULT_LANGUAGE, help=f"Sprachcode, Standard: {DEFAULT_LANGUAGE}")
    parser.add_argument("--speaking-rate", type=float, default=0.95, help="Sprechtempo, Standard: 0.95")
    parser.add_argument("--pitch", type=float, default=0.0, help="Tonhoehe, Standard: 0.0")
    parser.add_argument("--env-file", default=str(DEFAULT_ENV_FILE), help="Pfad zur lokalen Env-Datei.")
    parser.add_argument("--encoding", default="utf-8", help="Encoding der Eingabedatei, Standard: utf-8")
    parser.add_argument("--sleep", type=float, default=0.15, help="Pause zwischen Google-Anfragen.")
    return parser.parse_args()


def main() -> int:
    args = parse_args()
    load_env_file(Path(args.env_file))

    api_key = os.environ.get("GOOGLE_TTS_API_KEY", "").strip()
    if not api_key:
        raise SystemExit(
            "GOOGLE_TTS_API_KEY fehlt. Erwartet in "
            f"{args.env_file} als GOOGLE_TTS_API_KEY=\"...\"."
        )

    text = read_text(args)
    output_path = default_output_path(args)
    chunks = split_text(text)

    print(f"Erzeuge {output_path} mit {len(chunks)} Anfrage(n)...")
    audio_parts = []
    for index, chunk in enumerate(chunks, start=1):
        print(f"  Teil {index}/{len(chunks)}")
        audio_parts.append(
            synthesize_chunk(
                api_key=api_key,
                text=chunk,
                voice=args.voice,
                language_code=args.language_code,
                speaking_rate=args.speaking_rate,
                pitch=args.pitch,
            )
        )
        if args.sleep and index < len(chunks):
            time.sleep(args.sleep)

    write_mp3(output_path, audio_parts)
    print(f"Fertig: {output_path}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
