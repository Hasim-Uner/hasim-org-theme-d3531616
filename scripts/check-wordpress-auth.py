#!/usr/bin/env python3
"""Check the WordPress REST API user for the local application password."""

from __future__ import annotations

import base64
import json
import os
from pathlib import Path
from urllib import error, request


ENV_FILE = Path.home() / ".config" / "hasim-org" / "wordpress.env"


def load_env_file(path: Path) -> None:
    if not path.exists():
        raise SystemExit(f"Env-Datei fehlt: {path}")

    for raw_line in path.read_text(encoding="utf-8").splitlines():
        line = raw_line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue

        key, value = line.split("=", 1)
        os.environ.setdefault(key.strip(), value.strip().strip("'\"“”‘’"))


def require_env(name: str) -> str:
    value = os.environ.get(name, "").strip()
    if not value:
        raise SystemExit(f"{name} fehlt in {ENV_FILE}")
    return value


def main() -> int:
    load_env_file(ENV_FILE)

    site_url = require_env("WP_SITE_URL").rstrip("/")
    username = require_env("WP_USERNAME")
    app_password = require_env("WP_APP_PASSWORD")
    compact_password = "".join(char for char in app_password if char.isalnum())

    print(f"Site URL: {site_url}")
    print(f"Username-Laenge: {len(username)}")
    print(f"Username enthaelt @: {'ja' if '@' in username else 'nein'}")
    print(f"Application-Password-Zeichen: {len(app_password)}")
    print(f"Application-Password-Buchstaben/Zahlen: {len(compact_password)}")
    print(
        "Platzhalter gefunden: "
        + (
            "ja"
            if any(
                marker in f"{site_url} {username} {app_password}".lower()
                for marker in ["deine-domain", "dein-wordpress", "application-password", "hier"]
            )
            else "nein"
        )
    )

    token = base64.b64encode(f"{username}:{app_password}".encode("utf-8")).decode("ascii")

    req = request.Request(
        site_url + "/wp-json/wp/v2/users/me?context=edit",
        headers={"Authorization": f"Basic {token}"},
    )

    try:
        with request.urlopen(req, timeout=60) as response:
            user = json.loads(response.read().decode("utf-8"))
    except error.HTTPError as exc:
        detail = exc.read().decode("utf-8", errors="replace")
        raise SystemExit(f"WordPress Auth Fehler {exc.code}: {detail}") from exc
    except error.URLError as exc:
        raise SystemExit(f"WordPress konnte nicht erreicht werden: {exc}") from exc

    roles = user.get("roles", [])
    capabilities = user.get("capabilities", {}) or {}

    print(f"WordPress erkennt Benutzer: {user.get('username')}")
    print(f"Anzeigename: {user.get('name')}")
    print(f"Rollen: {', '.join(roles) if roles else '-'}")
    for capability in ["upload_files", "edit_posts", "publish_posts"]:
        print(f"{capability}: {'ja' if capabilities.get(capability) else 'nein'}")

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
