import json
from pathlib import Path

transcript = Path(
    r"C:\Users\KASIR ROKOK\.cursor\projects\d-WEB-APPS-SpotColor\agent-transcripts"
    r"\2e64a5c0-970d-4bba-a070-d0eb1a426d61\2e64a5c0-970d-4bba-a070-d0eb1a426d61.jsonl"
)
base = Path(r"d:\WEB APPS\LandingPageSoftware")
stop_marker = "di atas section pricelist saya melihat garis putih"

rels = [
    "public/css/landing.css",
    "resources/views/landing/components/hero.blade.php",
    "resources/views/landing/components/workflow.blade.php",
]

files = {rel: base / rel for rel in rels}
content = {rel: files[rel].read_text(encoding="utf-8") for rel in rels}

for line in transcript.read_text(encoding="utf-8").splitlines():
    if stop_marker in line:
        break
    try:
        obj = json.loads(line)
    except json.JSONDecodeError:
        continue
    if obj.get("role") != "assistant":
        continue
    for part in obj.get("message", {}).get("content", []):
        if part.get("type") != "tool_use":
            continue
        tool = part.get("name")
        inp = part.get("input", {})
        path_str = inp.get("path", "").replace("\\", "/")
        rel = next((r for r in rels if r in path_str), None)
        if not rel:
            continue
        if tool == "Write":
            content[rel] = inp.get("contents", "")
        elif tool == "StrReplace":
            old, new = inp.get("old_string"), inp.get("new_string")
            if old is None or new is None:
                continue
            text = content[rel]
            if old not in text:
                tn = text.replace("\r\n", "\n")
                on = old.replace("\r\n", "\n")
                if on not in tn:
                    print(f"SKIP not found in {rel}: {old[:80]!r}...")
                    continue
                content[rel] = tn.replace(on, new.replace("\r\n", "\n"), 1)
            else:
                n = -1 if inp.get("replace_all") else 1
                content[rel] = text.replace(old, new, n)

for rel in rels:
    files[rel].write_text(content[rel], encoding="utf-8", newline="\n")
    print(f"Wrote {rel} ({len(content[rel].splitlines())} lines)")
