# One-off: replay Write() calls from Cursor agent transcript into application/
# Source: .cursor/projects/.../agent-transcripts/2aa02f7d-.../....jsonl
import json
from pathlib import Path

TRANSCRIPT = Path(
    r"C:\Users\shoba\.cursor\projects\c-xampp-htdocs-property\agent-transcripts"
    r"\2aa02f7d-c5db-4479-bacf-3a7ca840bb09\2aa02f7d-c5db-4479-bacf-3a7ca840bb09.jsonl"
)
PROJECT = Path(r"c:\xampp\htdocs\property")


def walk_tools(obj, writes: dict):
    if isinstance(obj, dict):
        if obj.get("type") == "tool_use" and obj.get("name") == "Write":
            inp = obj.get("input") or {}
            p, c = inp.get("path"), inp.get("contents")
            if p and c is not None:
                writes[p] = c
        for v in obj.values():
            walk_tools(v, writes)
    elif isinstance(obj, list):
        for x in obj:
            walk_tools(x, writes)


def main():
    writes: dict[str, str] = {}
    with open(TRANSCRIPT, "r", encoding="utf-8", errors="replace") as f:
        for line in f:
            line = line.strip()
            if not line:
                continue
            try:
                rec = json.loads(line)
            except json.JSONDecodeError:
                continue
            walk_tools(rec, writes)

    app_files = {p: c for p, c in writes.items() if "application" in p.replace("\\", "/").lower()}
    written = 0
    for p, contents in sorted(app_files.items()):
        rel = Path(p)
        # Normalize to project-relative if path is absolute under property
        try:
            target = Path(p)
            if not target.is_absolute():
                target = PROJECT / target
        except Exception:
            target = PROJECT / p
        if "application" not in str(target).lower():
            continue
        target.parent.mkdir(parents=True, exist_ok=True)
        target.write_text(contents, encoding="utf-8", newline="\n")
        written += 1
        print("Wrote", target.relative_to(PROJECT))
    print("Done:", written, "files under application/")


if __name__ == "__main__":
    main()
