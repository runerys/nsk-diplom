# NSK Diplom Plugin

Et WordPress-plugin for NSK som lar medlemmer legge til og vise diplomer og priser klubben har mottatt.

## Funksjonalitet

### For besøkende:
- Visning av alle diplomer i omvendt kronologisk rekkefølge (nyeste først)
- Responsive grid-layout som viser diplomene som kort
- Lightbox-visning av bilder ved klikk
- Viser dato, tittel, bilder og beskrivende tekst for hvert diplom

### For innloggede brukere:
- Tilgang til skjema for å legge til nye diplomer
- Opplasting av bilder (diplom og stafettlag/arrangørgjeng)
- Enkelt skjema med validering

### For administratorer:
- Fullstendig administrasjon av diplomer i WordPress admin
- Media-bibliotek integrasjon for bildeopplasting
- Custom post type "Diplomer" med egne felter

## Installasjon

1. **Last opp plugin-filene:**
   - Kopier hele `nsk-diplom` mappen til `/wp-content/plugins/` på din WordPress-server
   - Eller pakk filene i en zip og last opp via WordPress admin under "Plugins" > "Add New" > "Upload Plugin"

2. **Aktiver pluginet:**
   - Gå til "Plugins" i WordPress admin
   - Finn "NSK Diplom Plugin" og klikk "Activate"

3. **Sett opp sider:**
   - Opprett en ny side kalt "Diplomer" med innholdet: `[nsk_diplomer]`
   - Opprett en ny side kalt "Legg til diplom" med innholdet: `[nsk_diplom_form]`
   - Sett permalink for "Diplomer"-siden til `/diplomer/`
   - Sett permalink for "Legg til diplom"-siden til `/legg-til-diplom/`

## Bruk

### Legge til nye diplomer

1. **Via frontend (anbefalt for medlemmer):**
   - Logg inn på WordPress
   - Gå til `/legg-til-diplom/` siden
   - Fyll ut skjemaet:
     - **Tittel:** Navn på diplom/pris
     - **Dato:** Når diplomen ble tildelt/arrangementet fant sted
     - **Diplom-bilde:** Last opp skannet bilde av diplomen
     - **Lag-bilde:** Last opp bilde av stafettlag eller arrangørgjeng
     - **Beskrivelse:** Forklarende tekst om prisen/arrangementet
   - Klikk "Legg til diplom"

2. **Via WordPress admin (for administratorer):**
   - Gå til "Diplomer" > "Legg til ny" i WordPress admin
   - Fyll ut alle feltene
   - Publiser

### Vise diplomer

Diplomene vises automatisk på siden med shortcode `[nsk_diplomer]`. Siden vil vise:
- Alle publiserte diplomer
- Sortert etter dato (nyeste først)
- Grid-layout med responsivt design
- Lenker til "Legg til diplom" for innloggede brukere

## Shortcodes

### `[nsk_diplomer]`
Viser alle diplomer i et grid-layout.

**Parametere:**
- `per_page` (valgfri): Antall diplomer per side (standard: 10)

**Eksempel:**
```
[nsk_diplomer per_page="20"]
```

### `[nsk_diplom_form]`
Viser skjema for å legge til nye diplomer (krever innlogging).

**Eksempel:**
```
[nsk_diplom_form]
```

## Tekniske detaljer

### Custom Post Type
Pluginet oppretter en custom post type `nsk_diplom` med følgende meta fields:
- `_nsk_tildeling_dato`: Dato for tildeling
- `_nsk_diplom_bilde`: ID for diplom-bilde (attachment)
- `_nsk_lag_bilde`: ID for lag-bilde (attachment)
- `_nsk_forklarende_tekst`: Beskrivende tekst

### Filer og struktur
```
nsk-diplom/
├── nsk-diplom-plugin.php    # Hovedplugin-fil
├── assets/
│   ├── style.css            # Frontend CSS
│   ├── script.js            # Frontend JavaScript
│   └── admin.js             # Admin JavaScript
└── README.md                # Denne filen
```

### Krav
- WordPress 5.0 eller nyere (testet opp til 6.8)
- PHP 7.2 eller nyere (anbefaler 7.4+)
- Brukere må være innlogget for å legge til diplomer

### PHP Kompatibilitet
| PHP Versjon | Status | Kommentar |
|-------------|---------|-----------|
| 7.2 | ✅ Støttet | Minimum versjon |
| 7.3 | ✅ Støttet | Fungerer fint |
| 7.4 | ✅ Anbefalt | God ytelse |
| 8.0 | ✅ Støttet | Utmerket ytelse |
| 8.1+ | ✅ Støttet | Beste ytelse |

## Sikkerhet

- Alle skjemaer bruker WordPress nonces for CSRF-beskyttelse
- Fileopplasting bruker WordPress' innebygde sikkerhetsfunksjoner
- Input blir sanitert og validert
- Kun innloggede brukere kan legge til diplomer

## Tilpasning

### CSS
For å tilpasse utseendet, legg til egne CSS-regler i ditt temas style.css:

```css
/* Endre diplom-kort bakgrunnsfarge */
.nsk-diplom-card {
    background-color: #f9f9f9;
}

/* Endre knapp-farge */
.nsk-add-diplom-btn {
    background-color: #your-color;
}
```

### Sortering
Som standard sorteres diplomene etter dato (nyeste først). For å endre dette, kan du modifisere `diplomer_shortcode` funksjonen i hovedfilen.

## Support

For spørsmål eller problemer med pluginet, kontakt NSK sin webansvarlige.

## Versjon

Versjon 1.1.0 - Mai 2025

### Endringer i v1.1.0:
- ✅ Testet og bekreftet kompatibilitet med WordPress 6.8
- ✅ Forbedret admin script loading for bedre ytelse
- ✅ Lagt til REST API støtte for fremtidig utvidelse
- ✅ Forbedret versjonskontroll og kompatibilitetssjekk

## 📊 Data Export og Backup

Pluginet inkluderer kraftige export-funksjoner for sikkerhetskopi og datamigrering:

### Export-formater:
- **CSV** - Excel/regneark-kompatibel
- **JSON** - API/programmering-vennlig
- **XML** - Strukturert markup  
- **SQL** - Database backup

### Bruk:
1. **Via Admin:** Gå til "Diplomer" → "Eksporter Data" i WordPress admin
2. **Via WP-CLI:** `wp nsk-diplom export csv --file=backup.csv`

### Database-dokumentasjon:
Se [DATABASE.md](DATABASE.md) for komplett beskrivelse av hvordan data lagres, SQL-queries for eksport, og optimalisering.
