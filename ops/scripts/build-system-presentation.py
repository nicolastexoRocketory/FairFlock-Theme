#!/usr/bin/env python3
"""FairFlock-System-Präsentation für Laien (Querformat A4, vertikal zentriert)."""

from __future__ import annotations

from pathlib import Path

from reportlab.lib.colors import HexColor, white
from reportlab.lib.pagesizes import A4, landscape
from reportlab.lib.units import mm
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.pdfgen import canvas

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / "docs" / "FairFlock-System-Praesentation.pdf"
LOGO = ROOT / "assets" / "images" / "logo.png"

BRAND = HexColor("#2F6B3A")
BRAND_DARK = HexColor("#1E4526")
BRAND_SOFT = HexColor("#E8F1EA")
INK = HexColor("#152018")
MUTED = HexColor("#4A5A4E")
LINE = HexColor("#D5E0D7")
CARD = HexColor("#F7FAF7")

PAGE = landscape(A4)
W, H = PAGE

# Safe content band (between top bar and footer)
TOP_SAFE = H - 18 * mm
BOTTOM_SAFE = 22 * mm
CONTENT_H = TOP_SAFE - BOTTOM_SAFE

FONT_DIR = Path("/System/Library/Fonts/Supplemental")
pdfmetrics.registerFont(TTFont("FF", str(FONT_DIR / "Arial.ttf")))
pdfmetrics.registerFont(TTFont("FF-Bold", str(FONT_DIR / "Arial Bold.ttf")))


def draw_bg(c: canvas.Canvas, variant: str = "default") -> None:
    c.setFillColor(white)
    c.rect(0, 0, W, H, fill=1, stroke=0)
    if variant == "title":
        c.setFillColor(BRAND_DARK)
        c.rect(0, 0, W * 0.40, H, fill=1, stroke=0)
        c.setFillColor(BRAND)
        path = c.beginPath()
        path.moveTo(W * 0.32, 0)
        path.lineTo(W * 0.46, 0)
        path.lineTo(W * 0.38, H)
        path.lineTo(W * 0.24, H)
        path.close()
        c.drawPath(path, fill=1, stroke=0)
    else:
        c.setFillColor(BRAND)
        c.rect(0, H - 7 * mm, W, 7 * mm, fill=1, stroke=0)
        c.setFillColor(BRAND_SOFT)
        c.rect(0, 0, W, 14 * mm, fill=1, stroke=0)


def footer(c: canvas.Canvas, page: int, total: int) -> None:
    c.setFillColor(MUTED)
    c.setFont("FF", 8)
    c.drawString(18 * mm, 5.5 * mm, "FairFlock Shop-System  ·  Rocketory / FairFlock GmbH")
    c.drawRightString(W - 18 * mm, 5.5 * mm, f"{page} / {total}")


def wrap_lines(c: canvas.Canvas, text: str, font: str, size: float, max_w: float) -> list[str]:
    words = text.split()
    lines: list[str] = []
    line = ""
    for w in words:
        trial = (line + " " + w).strip()
        if c.stringWidth(trial, font, size) <= max_w:
            line = trial
        else:
            if line:
                lines.append(line)
            line = w
    if line:
        lines.append(line)
    return lines or [""]


def measure_bullets(c: canvas.Canvas, items: list[str], width: float, size: float, leading: float) -> float:
    h = 0.0
    for item in items:
        lines = wrap_lines(c, item, "FF", size, width - 8 * mm)
        h += len(lines) * leading + 3.5 * mm
    return h


def draw_bullets(
    c: canvas.Canvas,
    items: list[str],
    x: float,
    y_top: float,
    width: float,
    size: float = 13,
    leading: float = 18,
) -> None:
    y = y_top
    for item in items:
        lines = wrap_lines(c, item, "FF", size, width - 8 * mm)
        c.setFillColor(BRAND)
        c.circle(x + 2 * mm, y + 1.4 * mm, 1.6 * mm, fill=1, stroke=0)
        c.setFillColor(INK)
        c.setFont("FF", size)
        for i, line in enumerate(lines):
            c.drawString(x + 8 * mm, y if i == 0 else y, line)
            y -= leading
        y -= 3.5 * mm


def vcenter_block(block_h: float) -> float:
    """Return top y of a block so it sits vertically centered in the content band."""
    return BOTTOM_SAFE + (CONTENT_H + block_h) / 2


def draw_heading_block(
    c: canvas.Canvas,
    title: str,
    subtitle: str,
    y_top: float,
) -> float:
    """Draw title+subtitle starting at y_top (baseline of title). Returns height used."""
    c.setFillColor(BRAND)
    c.setFont("FF-Bold", 9)
    c.drawString(22 * mm, y_top, "FAIRFLOCK SHOP-SYSTEM")
    c.setFillColor(INK)
    c.setFont("FF-Bold", 28)
    c.drawString(22 * mm, y_top - 12 * mm, title)
    used = 12 * mm + 10 * mm
    if subtitle:
        c.setFillColor(MUTED)
        c.setFont("FF", 14)
        for i, line in enumerate(wrap_lines(c, subtitle, "FF", 14, W - 50 * mm)):
            c.drawString(22 * mm, y_top - 12 * mm - 10 * mm - i * 6.5 * mm, line)
            used = 12 * mm + 10 * mm + (i + 1) * 6.5 * mm
    return used + 8 * mm


def card(
    c: canvas.Canvas,
    x: float,
    y: float,
    w: float,
    h: float,
    title: str,
    lines: list[str],
    title_size: float = 13,
    body_size: float = 11,
) -> None:
    c.setFillColor(CARD)
    c.setStrokeColor(LINE)
    c.setLineWidth(0.9)
    c.roundRect(x, y, w, h, 4 * mm, fill=1, stroke=1)
    c.setFillColor(BRAND_DARK)
    c.setFont("FF-Bold", title_size)
    c.drawString(x + 5 * mm, y + h - 9 * mm, title)
    c.setFillColor(INK)
    c.setFont("FF", body_size)
    ty = y + h - 17 * mm
    for line in lines:
        for wrapped in wrap_lines(c, line, "FF", body_size, w - 10 * mm):
            c.drawString(x + 5 * mm, ty, wrapped)
            ty -= 5.2 * mm
        ty -= 1.2 * mm


def build() -> Path:
    OUT.parent.mkdir(parents=True, exist_ok=True)
    c = canvas.Canvas(str(OUT), pagesize=PAGE)
    slides = []

    def add(fn):
        slides.append(fn)

    # —— 1 Titel ——
    def s_title(page, total):
        draw_bg(c, "title")
        left_block = 70 * mm
        y = (H + left_block) / 2
        c.setFillColor(white)
        c.setFont("FF-Bold", 11)
        c.drawString(18 * mm, y, "ROCKETORY  ·  FAIRFLOCK")
        c.setFont("FF-Bold", 32)
        c.drawString(18 * mm, y - 18 * mm, "Unser Shop-")
        c.drawString(18 * mm, y - 32 * mm, "System")
        c.setFont("FF", 12)
        c.drawString(18 * mm, y - 48 * mm, "Einfach erklärt —")
        c.drawString(18 * mm, y - 55 * mm, "für alle verständlich.")

        right_items = [
            "Eigener Online-Shop-Baukasten von uns",
            "Jede Firma sieht anders aus — Technik bleibt gleich",
            "Updates für alle Shops auf einmal möglich",
            "Viele fertige Funktionen — von Suche bis Kasse",
        ]
        rh = 18 * mm + measure_bullets(c, right_items, 120 * mm, 13, 18)
        ry = vcenter_block(rh)
        c.setFillColor(INK)
        c.setFont("FF-Bold", 20)
        c.drawString(W * 0.48, ry - 6 * mm, "Kurz gesagt")
        draw_bullets(c, right_items, W * 0.48, ry - 18 * mm, 125 * mm, size=13, leading=18)

        if LOGO.exists():
            c.drawImage(
                str(LOGO),
                W - 46 * mm,
                18 * mm,
                width=26 * mm,
                height=26 * mm,
                mask="auto",
                preserveAspectRatio=True,
                anchor="c",
            )
        footer(c, page, total)

    # —— 2 Agenda ——
    def s_agenda(page, total):
        draw_bg(c)
        items = [
            ("1", "Die Idee", "Warum wir ein eigenes System gebaut haben"),
            ("2", "Alle Funktionen", "Was der Shop und das Büro können"),
            ("3", "Alltag im Büro", "Einrichten und pflegen — ohne Technik-Studium"),
            ("4", "Aktuell & sicher", "Updates und Schutz — ohne Drama"),
            ("5", "Statt Flatsome", "Der einfache Vergleich"),
        ]
        head_h = 28 * mm
        rows_h = len(items) * 18 * mm
        block = head_h + rows_h
        y_top = vcenter_block(block)
        draw_heading_block(c, "Überblick", "Fünf Themen — ohne Fachchinesisch", y_top)
        y = y_top - head_h
        for num, title, sub in items:
            c.setFillColor(BRAND_SOFT)
            c.roundRect(22 * mm, y - 5 * mm, W - 44 * mm, 15 * mm, 3 * mm, fill=1, stroke=0)
            c.setFillColor(BRAND)
            c.setFont("FF-Bold", 16)
            c.drawString(28 * mm, y + 1 * mm, num)
            c.setFillColor(INK)
            c.setFont("FF-Bold", 14)
            c.drawString(42 * mm, y + 2.5 * mm, title)
            c.setFillColor(MUTED)
            c.setFont("FF", 11)
            c.drawString(42 * mm, y - 3 * mm, sub)
            y -= 18 * mm
        footer(c, page, total)

    # —— 3 Idee ——
    def s_idea(page, total):
        draw_bg(c)
        bullets = [
            "Wir bauen Online-Shops für Vereine, Clubs und Unternehmen.",
            "Statt eines fertigen „Alles-drin“-Themes von fremden Anbietern haben wir unser eigenes System.",
            "Ein System für viele Shops: gleiche Qualität, individuelles Aussehen (Logo, Farben, Texte).",
            "Vorteil: einmal verbessern — alle Shops können mitwachsen.",
        ]
        head_h = 30 * mm
        body_h = measure_bullets(c, bullets, W - 50 * mm, 14, 20)
        block = head_h + body_h
        y_top = vcenter_block(block)
        used = draw_heading_block(
            c,
            "1. Die Idee",
            "Ein Shop-System von uns — für viele Marken",
            y_top,
        )
        draw_bullets(c, bullets, 22 * mm, y_top - used, W - 50 * mm, size=14, leading=20)
        footer(c, page, total)

    # —— 4 Idee Vorteile ——
    def s_idea2(page, total):
        draw_bg(c)
        cards = [
            ("Leicht", ["Nur das, was wir brauchen", "Schnelle Seiten", "Weniger Ballast"]),
            ("Von uns", ["Wir kennen den Code", "Schnelle Anpassungen", "Kein Warten auf Fremdfirmen"]),
            ("Einfach", ["Klar im Alltag bedienbar", "Fertige Bausteine", "Weniger Fehlbedienung"]),
            ("Für viele Shops", ["Ein Update — viele Seiten", "Jede Marke bleibt einzigartig", "Weniger Doppelarbeit"]),
        ]
        head_h = 28 * mm
        card_h = 78 * mm
        block = head_h + card_h + 4 * mm
        y_top = vcenter_block(block)
        draw_heading_block(c, "1. Was das bringt", "Vier Vorteile auf einen Blick", y_top)
        y_cards = y_top - head_h - card_h
        x = 18 * mm
        for title, lines in cards:
            card(c, x, y_cards, 64 * mm, card_h, title, lines, title_size=14, body_size=12)
            x += 68 * mm
        footer(c, page, total)

    # —— 5 Funktionen für Kunden ——
    def s_front(page, total):
        draw_bg(c)
        cards = [
            (
                "Finden & Orientieren",
                [
                    "Suche mit Produktvorschlägen",
                    "Menü und Untermenüs",
                    "Kategorien & Kollektionen",
                    "Konto-Zugang (Login)",
                    "Warenkorb von der Seite",
                ],
            ),
            (
                "Produkte & Kaufen",
                [
                    "Produkt-Schnellansicht (Popup)",
                    "Produkt-Slider / Teaser",
                    "Logo oder Text aufs Produkt",
                    "Warenkorb, Kasse, Bestätigung",
                    "Versand- & Zahlungsseiten",
                ],
            ),
            (
                "Vertrauen & Inhalt",
                [
                    "Hero mit großen Bildern",
                    "Vertrauens-Leiste",
                    "FAQ, Schritte, Bewertungen",
                    "Zitat / Marken-Band",
                    "Klare Call-to-Actions",
                ],
            ),
        ]
        head_h = 30 * mm
        card_h = 100 * mm
        block = head_h + card_h
        y_top = vcenter_block(block)
        draw_heading_block(
            c,
            "2. Funktionen für Kundinnen & Kunden",
            "Alles, was man im Shop erleben kann",
            y_top,
        )
        y_cards = y_top - head_h - card_h
        x = 18 * mm
        for title, lines in cards:
            card(c, x, y_cards, 84 * mm, card_h, title, lines, title_size=14, body_size=12)
            x += 90 * mm
        footer(c, page, total)

    # —— 6 Funktionen fürs Büro / Setup ——
    def s_all_functions(page, total):
        draw_bg(c)
        cards = [
            (
                "Einrichten",
                [
                    "Logo & Farben je Marke",
                    "Fertige Seiten-Bausteine",
                    "Ein-Klick-Startseite",
                    "Rechtliche Platzhalter-Seiten",
                    "Kategorien-Seite",
                    "Plugin-Checkliste zum Start",
                ],
            ),
            (
                "Shop-Alltag",
                [
                    "Produkte & Preise pflegen",
                    "Personalisierung je Artikel",
                    "Passwortschutz für den Shop",
                    "Einzelne Seiten absichern",
                    "FAQ & Inhalte bearbeiten",
                    "Handy & Desktop tauglich",
                ],
            ),
            (
                "Betrieb",
                [
                    "Ein System für viele Shops",
                    "Zentrale Verbesserungen",
                    "Probelauf vor Live-Update",
                    "Kundeninhalte bleiben sicher",
                    "Schnelle Shop-Seiten",
                    "Weniger Fremd-Abhängigkeit",
                ],
            ),
        ]
        head_h = 32 * mm
        card_h = 108 * mm
        block = head_h + card_h
        y_top = vcenter_block(block)
        draw_heading_block(
            c,
            "2. Funktionen fürs Büro & den Betrieb",
            "Was man einrichten, pflegen und steuern kann",
            y_top,
        )
        y_cards = y_top - head_h - card_h
        x = 18 * mm
        for title, lines in cards:
            card(c, x, y_cards, 84 * mm, card_h, title, lines, title_size=14, body_size=11.5)
            x += 90 * mm
        footer(c, page, total)

    # —— 7 Backend ——
    def s_backend(page, total):
        draw_bg(c)
        steps = [
            ("1", "Plugins prüfen", "WooCommerce, Complianz, optional Yoast SEO."),
            ("2", "Marke festlegen", "Logo und Farben — einmal einstellen."),
            ("3", "Layouts anlegen", "Bei Bedarf fertige Startseite per Klick."),
            ("4", "Shop pflegen", "Produkte, Texte, Bilder — laufend aktuell halten."),
        ]
        head_h = 30 * mm
        steps_h = len(steps) * 22 * mm
        block = head_h + steps_h
        y_top = vcenter_block(block)
        draw_heading_block(
            c,
            "3. So arbeitet man damit",
            "Vom leeren Shop zum laufenden Alltag",
            y_top,
        )
        y = y_top - head_h
        for num, title, text in steps:
            c.setFillColor(BRAND)
            c.circle(32 * mm, y + 1 * mm, 7 * mm, fill=1, stroke=0)
            c.setFillColor(white)
            c.setFont("FF-Bold", 14)
            c.drawCentredString(32 * mm, y - 1.5 * mm, num)
            c.setFillColor(INK)
            c.setFont("FF-Bold", 15)
            c.drawString(48 * mm, y + 3 * mm, title)
            c.setFillColor(MUTED)
            c.setFont("FF", 13)
            c.drawString(48 * mm, y - 5 * mm, text)
            y -= 22 * mm
        footer(c, page, total)

    # —— 8 Backend Demo ——
    def s_backend_demo(page, total):
        draw_bg(c)
        bullets = [
            "Unter „FairFlock → Demo Content“ sieht man zuerst die empfohlenen Plugins.",
            "Danach wählt man aus, welche Layouts angelegt werden sollen.",
            "Zum Beispiel: komplette Startseite, rechtliche Seiten, Kategorien-Seite.",
            "Bestehende Inhalte werden nur überschrieben, wenn man das ausdrücklich erlaubt.",
        ]
        head_h = 32 * mm
        body_h = measure_bullets(c, bullets, W - 50 * mm, 14, 20)
        note_h = 30 * mm
        block = head_h + body_h + 6 * mm + note_h
        y_top = vcenter_block(block)
        used = draw_heading_block(
            c,
            "3. Demo Content im Backend",
            "Plugins prüfen und fertige Layouts anlegen",
            y_top,
        )
        draw_bullets(c, bullets, 22 * mm, y_top - used, W - 50 * mm, size=14, leading=20)
        note_top = y_top - used - body_h - 4 * mm
        c.setFillColor(BRAND_DARK)
        c.roundRect(22 * mm, note_top - note_h + 4 * mm, W - 44 * mm, note_h, 3 * mm, fill=1, stroke=0)
        c.setFillColor(white)
        c.setFont("FF-Bold", 12)
        c.drawString(30 * mm, note_top - 8 * mm, "Wichtig")
        c.setFont("FF", 12)
        c.drawString(
            30 * mm,
            note_top - 16 * mm,
            "Nichts passiert von allein bei Updates — nur was jemand bewusst anklickt.",
        )
        footer(c, page, total)

    # —— 9 Updates ——
    def s_updates(page, total):
        draw_bg(c)
        bullets = [
            "Verbesserungen werden zentral vorbereitet und getestet.",
            "Danach können alle Shops dasselbe Update bekommen.",
            "Texte, Bilder und Farben der Kunden bleiben erhalten.",
            "Vor dem Live-Gang gibt es einen Probelauf — Sicherheit zuerst.",
        ]
        head_h = 30 * mm
        body_h = measure_bullets(c, bullets, W - 50 * mm, 14, 20)
        block = head_h + body_h
        y_top = vcenter_block(block)
        used = draw_heading_block(
            c,
            "4. Immer aktuell",
            "Einmal pflegen — viele Shops profitieren",
            y_top,
        )
        draw_bullets(c, bullets, 22 * mm, y_top - used, W - 50 * mm, size=14, leading=20)
        footer(c, page, total)

    # —— 10 Sicherheit ——
    def s_security(page, total):
        draw_bg(c)
        cards = [
            (
                "Passwort-Tür",
                [
                    "Shop bei Bedarf abschließen",
                    "Nur mit Passwort hinein",
                    "Mit Firmenlogo begrüßen",
                    "Admins kommen trotzdem rein",
                ],
            ),
            (
                "Uploads & Formulare",
                [
                    "Nur erlaubte Dateitypen",
                    "Größenbegrenzung",
                    "Texte werden geprüft",
                    "Keine Geheimnisse im Code",
                ],
            ),
            (
                "Ruhe bei Updates",
                [
                    "Kundeninhalte bleiben",
                    "Kein „alles weg“-Risiko",
                    "Erst testen, dann live",
                    "Klare Freigabe nötig",
                ],
            ),
        ]
        head_h = 30 * mm
        card_h = 90 * mm
        block = head_h + card_h
        y_top = vcenter_block(block)
        draw_heading_block(c, "4. Sicherheit", "Schutz, den man erklären kann", y_top)
        y_cards = y_top - head_h - card_h
        x = 18 * mm
        for title, lines in cards:
            card(c, x, y_cards, 84 * mm, card_h, title, lines, title_size=14, body_size=12)
            x += 90 * mm
        footer(c, page, total)

    # —— 11 Vergleich ——
    def s_compare(page, total):
        draw_bg(c)
        rows = [
            ("Thema", "FairFlock", "Flatsome (typisch)"),
            ("Für wen?", "Genau unsere Shops", "Möglichst alles für alle"),
            ("Bedienung", "Wenige, klare Wege", "Viele Extra-Optionen"),
            ("Aussehen", "Marke über Farben/Logo", "Oft über Theme-Einstellungen"),
            ("Updates", "Zentral für viele Shops", "Oft Shop für Shop"),
            ("Abhängigkeit", "Unser System", "Fremdes Groß-Theme"),
            ("Risiko", "Übersichtlich", "Mehr Stellschrauben = mehr Fehler"),
        ]
        widths = [50 * mm, 100 * mm, 100 * mm]
        head_h = 28 * mm
        table_h = len(rows) * 11 * mm
        block = head_h + table_h
        y_top = vcenter_block(block)
        draw_heading_block(c, "5. FairFlock oder Flatsome?", "Der Vergleich in Alltagssprache", y_top)
        y = y_top - head_h
        x0 = 22 * mm
        for i, row in enumerate(rows):
            h = 11 * mm
            if i == 0:
                c.setFillColor(BRAND_DARK)
            elif i % 2 == 0:
                c.setFillColor(BRAND_SOFT)
            else:
                c.setFillColor(white)
            c.rect(x0, y - h + 3 * mm, sum(widths), h, fill=1, stroke=0)
            x = x0
            for j, (col, width) in enumerate(zip(row, widths)):
                c.setFillColor(white if i == 0 else INK)
                c.setFont("FF-Bold" if i == 0 or j == 0 else "FF", 11 if i else 11)
                c.drawString(x + 3 * mm, y - 3 * mm, col)
                x += width
            y -= h
        footer(c, page, total)

    # —— 12 Warum ——
    def s_why(page, total):
        draw_bg(c)
        bullets = [
            "Flatsome kann viel — wir brauchen das Richtige.",
            "Weniger Knöpfe bedeuten weniger Verwirrung und weniger Fehler.",
            "Funktionen wie Passwortschutz oder Ein-Klick-Startseite erreichen alle Shops gemeinsam.",
            "Langfristig spart das Zeit, Geld und Nerven — bei gleichbleibend guter Qualität.",
        ]
        head_h = 30 * mm
        body_h = measure_bullets(c, bullets, W - 50 * mm, 14, 20)
        quote_h = 30 * mm
        block = head_h + body_h + quote_h + 6 * mm
        y_top = vcenter_block(block)
        used = draw_heading_block(c, "5. Unsere Entscheidung", "Kontrolle, Tempo, Ruhe", y_top)
        draw_bullets(c, bullets, 22 * mm, y_top - used, W - 50 * mm, size=14, leading=20)
        note_top = y_top - used - body_h - 4 * mm
        c.setFillColor(BRAND)
        c.roundRect(22 * mm, note_top - 28 * mm, W - 44 * mm, 30 * mm, 3 * mm, fill=1, stroke=0)
        c.setFillColor(white)
        c.setFont("FF-Bold", 13)
        c.drawString(30 * mm, note_top - 10 * mm, "In einem Satz")
        c.setFont("FF", 12)
        c.drawString(30 * mm, note_top - 18 * mm, "FairFlock ist keine gekaufte Fertiglösung von der Stange —")
        c.drawString(30 * mm, note_top - 25 * mm, "sondern unsere gemeinsame Shop-Plattform für viele Marken.")
        footer(c, page, total)

    # —— 13 Abschluss ——
    def s_close(page, total):
        draw_bg(c, "title")
        left_h = 55 * mm
        y = (H + left_h) / 2
        c.setFillColor(white)
        c.setFont("FF-Bold", 30)
        c.drawString(18 * mm, y, "Danke.")
        c.setFont("FF", 13)
        c.drawString(18 * mm, y - 16 * mm, "Fragen? Gerne live zeigen.")
        c.setFont("FF", 10)
        c.drawString(18 * mm, 22 * mm, "Intern  ·  FairFlock Shop-System")

        nexts = [
            "Kurz am Bildschirm zeigen (Shop + Backend)",
            "Funktionen live durchgehen",
            "Entscheiden: Rollout auf weitere Shops",
        ]
        rh = 16 * mm + measure_bullets(c, nexts, 120 * mm, 13, 18)
        ry = vcenter_block(rh)
        c.setFillColor(INK)
        c.setFont("FF-Bold", 18)
        c.drawString(W * 0.48, ry - 4 * mm, "Als Nächstes")
        draw_bullets(c, nexts, W * 0.48, ry - 16 * mm, 120 * mm, size=13, leading=18)
        footer(c, page, total)

    for fn in (
        s_title,
        s_agenda,
        s_idea,
        s_idea2,
        s_front,
        s_all_functions,
        s_backend,
        s_backend_demo,
        s_updates,
        s_security,
        s_compare,
        s_why,
        s_close,
    ):
        add(fn)

    total = len(slides)
    for i, fn in enumerate(slides, start=1):
        fn(i, total)
        c.showPage()
    c.save()
    return OUT


if __name__ == "__main__":
    path = build()
    print(f"Wrote {path} ({path.stat().st_size} bytes)")
