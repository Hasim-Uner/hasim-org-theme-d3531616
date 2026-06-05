#!/usr/bin/env python3
"""Upload an MP3 file to the WordPress media library via REST API."""

from __future__ import annotations

import argparse
import base64
import json
import mimetypes
import os
import sys
from pathlib import Path
from urllib import error, request


DEFAULT_ENV_FILE = Path.home() / ".config" / "hasim-org" / "wordpress.env"


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


def env_value(name: str, env_file: str) -> str:
    value = os.environ.get(name, "").strip()
    if value:
        return value
    raise SystemExit(f"{name} fehlt. Erwartet in {env_file}.")


def build_auth_header(username: str, app_password: str) -> str:
    token = base64.b64encode(f"{username}:{app_password}".encode("utf-8")).decode("ascii")
    return f"Basic {token}"


def upload_media(
    site_url: str,
    username: str,
    app_password: str,
    file_path: Path,
    title: str,
    alt_text: str,
) -> dict:
    mime_type = mimetypes.guess_type(file_path.name)[0] or "audio/mpeg"
    endpoint = site_url.rstrip("/") + "/wp-json/wp/v2/media"

    headers = {
        "Authorization": build_auth_header(username, app_password),
        "Content-Type": mime_type,
        "Content-Disposition": f'attachment; filename="{file_path.name}"',
    }

    req = request.Request(
        endpoint,
        data=file_path.read_bytes(),
        headers=headers,
        method="POST",
    )

    try:
        with request.urlopen(req, timeout=120) as response:
            media = json.loads(response.read().decode("utf-8"))
    except error.HTTPError as exc:
        detail = exc.read().decode("utf-8", errors="replace")
        raise SystemExit(f"WordPress Upload Fehler {exc.code}: {detail}") from exc
    except error.URLError as exc:
        raise SystemExit(f"WordPress konnte nicht erreicht werden: {exc}") from exc

    if title or alt_text:
        update_media_metadata(site_url, username, app_password, int(media["id"]), title, alt_text)

        with request.urlopen(
            request.Request(
                endpoint + f"/{int(media['id'])}",
                headers={"Authorization": build_auth_header(username, app_password)},
                method="GET",
            ),
            timeout=60,
        ) as response:
            media = json.loads(response.read().decode("utf-8"))

    return media


def update_media_metadata(
    site_url: str,
    username: str,
    app_password: str,
    media_id: int,
    title: str,
    alt_text: str,
) -> None:
    endpoint = site_url.rstrip("/") + f"/wp-json/wp/v2/media/{media_id}"
    payload: dict[str, object] = {}
    if title:
        payload["title"] = title
    if alt_text:
        payload["alt_text"] = alt_text

    req = request.Request(
        endpoint,
        data=json.dumps(payload).encode("utf-8"),
        headers={
            "Authorization": build_auth_header(username, app_password),
            "Content-Type": "application/json; charset=utf-8",
        },
        method="POST",
    )

    try:
        with request.urlopen(req, timeout=60):
            return
    except error.HTTPError as exc:
        detail = exc.read().decode("utf-8", errors="replace")
        raise SystemExit(f"WordPress Metadata Fehler {exc.code}: {detail}") from exc


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Laedt eine MP3-Datei in die WordPress-Mediathek hoch."
    )
    parser.add_argument("file", help="Pfad zur MP3-Datei.")
    parser.add_argument("--title", default="", help="Titel in der WordPress-Mediathek.")
    parser.add_argument("--alt-text", default="", help="Alternativtext/Beschreibung.")
    parser.add_argument("--env-file", default=str(DEFAULT_ENV_FILE), help="Pfad zur lokalen WordPress-Env-Datei.")
    return parser.parse_args()


def main() -> int:
    args = parse_args()
    load_env_file(Path(args.env_file))

    file_path = Path(args.file)
    if not file_path.exists():
        raise SystemExit(f"Datei nicht gefunden: {file_path}")
    if file_path.suffix.lower() != ".mp3":
        raise SystemExit("Bitte eine MP3-Datei hochladen.")

    site_url = env_value("WP_SITE_URL", args.env_file)
    username = env_value("WP_USERNAME", args.env_file)
    app_password = env_value("WP_APP_PASSWORD", args.env_file)

    title = args.title or file_path.stem.replace("-", " ").replace("_", " ")
    media = upload_media(site_url, username, app_password, file_path, title, args.alt_text)

    print("Upload fertig.")
    print(f"Media ID: {media.get('id')}")
    print(f"URL: {media.get('source_url')}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
