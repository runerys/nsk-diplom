# NSK Diplom Plugin - Database Dokumentasjon

## Oversikt
Dette dokumentet beskriver hvordan NSK Diplom Plugin lagrer data i WordPress-databasen. Informasjonen er viktig for backup, migrering, og eventuelle fremtidige konverteringer.

## Database Struktur

### Hovedtabeller som brukes:
1. `wp_posts` - Hoveddata for diplomer
2. `wp_postmeta` - Metadata (custom fields)
3. `wp_posts` (attachments) - Bildeopplastinger

## Detaljert Datalagringsstruktur

### 1. Hovedpost (wp_posts tabell)

Hver diplom lagres som en post i `wp_posts` tabellen med følgende felter:

```sql
-- Eksempel på en diplom-post i wp_posts
ID                  : [Auto-increment nummer]
post_author         : [WordPress bruker-ID som opprettet diplomen]
post_date           : [Dato når diplomen ble opprettet i systemet]
post_date_gmt       : [GMT versjon av post_date]
post_content        : [Tom eller innhold fra WordPress editor]
post_title          : [Tittel på diplom/pris - f.eks. "Stafettgull 2024"]
post_excerpt        : [Tom]
post_status         : 'publish'
post_comment_status : 'closed'
post_ping_status    : 'closed'
post_password       : [Tom]
post_name           : [URL-slug generert fra tittel]
to_ping             : [Tom]
pinged              : [Tom]
post_modified       : [Sist endret dato]
post_modified_gmt   : [GMT versjon av post_modified]
post_content_filtered: [Tom]
post_parent         : 0
guid                : [WordPress URL til posten]
menu_order          : 0
post_type           : 'nsk_diplom'
post_mime_type      : [Tom]
comment_count       : 0
```

### 2. Metadata (wp_postmeta tabell)

Custom fields lagres i `wp_postmeta` tabellen knyttet til hver diplom via `post_id`:

```sql
-- Metadata for hver diplom
meta_id    : [Auto-increment]
post_id    : [ID fra wp_posts tabellen]
meta_key   : '_nsk_tildeling_dato'
meta_value : 'YYYY-MM-DD' (f.eks. '2024-05-15')

meta_id    : [Auto-increment]
post_id    : [ID fra wp_posts tabellen]
meta_key   : '_nsk_diplom_bilde'
meta_value : '[Attachment ID]' (f.eks. '123')

meta_id    : [Auto-increment]
post_id    : [ID fra wp_posts tabellen]
meta_key   : '_nsk_lag_bilde'
meta_value : '[Attachment ID]' (f.eks. '124')

meta_id    : [Auto-increment]
post_id    : [ID fra wp_posts tabellen]
meta_key   : '_nsk_forklarende_tekst'
meta_value : 'Beskrivende tekst om diplomen...'

meta_id    : [Auto-increment]
post_id    : [ID fra wp_posts tabellen]
meta_key   : '_thumbnail_id'
meta_value : '[Attachment ID]' (samme som _nsk_diplom_bilde)
```

### 3. Bildeopplastinger (wp_posts tabell - attachments)

Bilder lagres som separate poster i `wp_posts` med `post_type = 'attachment'`:

```sql
-- Eksempel på et bilde-attachment
ID                  : [Auto-increment nummer]
post_author         : [WordPress bruker-ID]
post_date           : [Opplastingsdato]
post_date_gmt       : [GMT versjon]
post_content        : [Tom]
post_title          : [Bildenavn uten filtype]
post_excerpt        : [Tom]
post_status         : 'inherit'
post_comment_status : 'open'
post_ping_status    : 'closed'
post_password       : [Tom]
post_name           : [URL-slug av bildenavn]
to_ping             : [Tom]
pinged              : [Tom]
post_modified       : [Sist endret]
post_modified_gmt   : [GMT versjon]
post_content_filtered: [Tom]
post_parent         : [ID til diplom-posten dette bildet tilhører]
guid                : [Full URL til bildefilen]
menu_order          : 0
post_type           : 'attachment'
post_mime_type      : 'image/jpeg' (eller image/png, etc.)
comment_count       : 0
```

## Metadata Keys Forklaring

| Meta Key | Datatype | Beskrivelse | Eksempel |
|----------|----------|-------------|----------|
| `_nsk_tildeling_dato` | Date (YYYY-MM-DD) | Dato for når diplomen ble tildelt | '2024-05-15' |
| `_nsk_diplom_bilde` | Integer | Attachment ID for diplom-bildet | '123' |
| `_nsk_lag_bilde` | Integer | Attachment ID for lag/arrangør-bildet | '124' |
| `_nsk_forklarende_tekst` | Text | Beskrivende tekst om diplomen | 'Vunnet på Holmenkollstafetten...' |
| `_thumbnail_id` | Integer | WordPress standard featured image (kopierer _nsk_diplom_bilde) | '123' |

## SQL Queries for Data Eksport

### Hent alle diplomer med metadata:

```sql
SELECT 
    p.ID,
    p.post_title,
    p.post_date,
    p.post_author,
    p.post_status,
    MAX(CASE WHEN pm.meta_key = '_nsk_tildeling_dato' THEN pm.meta_value END) as tildeling_dato,
    MAX(CASE WHEN pm.meta_key = '_nsk_diplom_bilde' THEN pm.meta_value END) as diplom_bilde_id,
    MAX(CASE WHEN pm.meta_key = '_nsk_lag_bilde' THEN pm.meta_value END) as lag_bilde_id,
    MAX(CASE WHEN pm.meta_key = '_nsk_forklarende_tekst' THEN pm.meta_value END) as forklarende_tekst
FROM wp_posts p
LEFT JOIN wp_postmeta pm ON p.ID = pm.post_id
WHERE p.post_type = 'nsk_diplom'
    AND p.post_status = 'publish'
GROUP BY p.ID
ORDER BY tildeling_dato DESC;
```

### Hent komplett datasett med bildeinfo:

```sql
SELECT 
    d.ID as diplom_id,
    d.post_title as diplom_tittel,
    d.post_date as opprettet_dato,
    pm1.meta_value as tildeling_dato,
    pm2.meta_value as forklarende_tekst,
    diplom_img.guid as diplom_bilde_url,
    diplom_img.post_title as diplom_bilde_navn,
    lag_img.guid as lag_bilde_url,
    lag_img.post_title as lag_bilde_navn,
    u.display_name as opprettet_av
FROM wp_posts d
LEFT JOIN wp_postmeta pm1 ON d.ID = pm1.post_id AND pm1.meta_key = '_nsk_tildeling_dato'
LEFT JOIN wp_postmeta pm2 ON d.ID = pm2.post_id AND pm2.meta_key = '_nsk_forklarende_tekst'
LEFT JOIN wp_postmeta pm3 ON d.ID = pm3.post_id AND pm3.meta_key = '_nsk_diplom_bilde'
LEFT JOIN wp_postmeta pm4 ON d.ID = pm4.post_id AND pm4.meta_key = '_nsk_lag_bilde'
LEFT JOIN wp_posts diplom_img ON pm3.meta_value = diplom_img.ID
LEFT JOIN wp_posts lag_img ON pm4.meta_value = lag_img.ID
LEFT JOIN wp_users u ON d.post_author = u.ID
WHERE d.post_type = 'nsk_diplom'
    AND d.post_status = 'publish'
ORDER BY pm1.meta_value DESC;
```

## Backup Strategi

### 1. WordPress Export (XML)
WordPress' innebygde eksportfunksjon vil inkludere:
- Alle diplom-poster
- Metadata
- Attachments (men ikke selve filene)

### 2. Database Backup
For komplett backup, eksporter disse tabellene:
```sql
-- Kun diplom-relaterte data
SELECT * FROM wp_posts WHERE post_type = 'nsk_diplom';
SELECT * FROM wp_posts WHERE post_type = 'attachment' AND post_parent IN 
    (SELECT ID FROM wp_posts WHERE post_type = 'nsk_diplom');
SELECT * FROM wp_postmeta WHERE post_id IN 
    (SELECT ID FROM wp_posts WHERE post_type = 'nsk_diplom');
```

### 3. Fil Backup
Ikke glem å ta backup av bildene i:
- `/wp-content/uploads/YYYY/MM/` (organisert etter opplastingsdato)

## Konvertering til Andre Systemer

### CSV Export Format
For enkel konvertering kan du eksportere til CSV:

```csv
ID,Tittel,Tildeling_Dato,Forklarende_Tekst,Diplom_Bilde_URL,Lag_Bilde_URL,Opprettet_Dato,Opprettet_Av
1,"Stafettgull 2024","2024-05-15","Vunnet på Holmenkollstafetten","http://site.com/wp-content/uploads/2024/05/diplom1.jpg","http://site.com/wp-content/uploads/2024/05/lag1.jpg","2024-05-20 10:30:00","admin"
```

### JSON Export Format
For API-basert konvertering:

```json
{
  "diplomer": [
    {
      "id": 1,
      "tittel": "Stafettgull 2024",
      "tildeling_dato": "2024-05-15",
      "forklarende_tekst": "Vunnet på Holmenkollstafetten...",
      "diplom_bilde": {
        "id": 123,
        "url": "http://site.com/wp-content/uploads/2024/05/diplom1.jpg",
        "filename": "diplom1.jpg"
      },
      "lag_bilde": {
        "id": 124,
        "url": "http://site.com/wp-content/uploads/2024/05/lag1.jpg",
        "filename": "lag1.jpg"
      },
      "opprettet_dato": "2024-05-20T10:30:00",
      "opprettet_av": "admin"
    }
  ]
}
```

## Migrering til Nytt System

### Steg 1: Eksporter data
1. Kjør SQL queries ovenfor
2. Last ned alle bilder fra uploads-mappen
3. Ta full database backup

### Steg 2: Konverter data
1. Transformer til ønsket format
2. Håndter bildestier/URLs
3. Valider data integritet

### Steg 3: Importer til nytt system
1. Opprett tilsvarende struktur
2. Importer data
3. Oppdater bildestier
4. Test funksjonalitet

## Vedlikehold og Oppgradering

### Regelmessige oppgaver:
1. **Database optimalisering**: Fjern ubrukte metadata
2. **Bilde cleanup**: Fjern orphaned attachments
3. **Data validering**: Sjekk at alle diplomer har nødvendige felt

### Cleanup SQL:
```sql
-- Finn diplomer uten tildeling_dato
SELECT p.ID, p.post_title 
FROM wp_posts p 
LEFT JOIN wp_postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_nsk_tildeling_dato'
WHERE p.post_type = 'nsk_diplom' AND pm.meta_value IS NULL;

-- Finn orphaned attachments
SELECT p.ID, p.post_title, p.guid
FROM wp_posts p
WHERE p.post_type = 'attachment' 
    AND p.post_parent NOT IN (SELECT ID FROM wp_posts WHERE post_type = 'nsk_diplom');
```

## Sikkerhet og Tilgangskontroll

Data er beskyttet av WordPress' standard sikkerhetssystem:
- Kun innloggede brukere kan opprette diplomer
- WordPress capabilities kontrollerer redigering
- Metadata er beskyttet med underscore-prefix (_)

## Data Export Tool

For enkel eksport av diplom-data til forskjellige formater, brukes export-scriptet som følger med pluginet:

### Tilgjengelige formater:
- **CSV**: Excel/regneark-kompatibel
- **JSON**: API/programmering-vennlig  
- **XML**: Strukturert markup
- **SQL**: Database backup

### Bruk via WordPress Admin:
1. Gå til "Diplomer" → "Eksporter Data" i admin-panelet
2. Velg ønsket format
3. Last ned filen

### Bruk via WP-CLI:
```bash
# Eksporter til CSV
wp nsk-diplom export csv --file=diplomer.csv

# Eksporter til JSON (stdout)
wp nsk-diplom export json

# Eksporter til XML fil
wp nsk-diplom export xml --file=diplomer.xml
```

### Eksempel CSV-eksport:
```csv
ID;Tittel;Innhold;Tildeling Dato;Forklarende Tekst;Opprettet Dato;Sist Endret;Forfatter;Forfatter Epost;Status;Diplom Bilde URL;Diplom Bilde Navn;Lag Bilde URL;Lag Bilde Navn
123;"Stafettgull 2024";"";"2024-05-15";"Vunnet på Holmenkollstafetten";"2024-05-20 10:30:00";"2024-05-20 10:30:00";"Admin";"admin@nsk.no";"publish";"http://nsk.no/wp-content/uploads/2024/05/diplom1.jpg";"diplom1";"http://nsk.no/wp-content/uploads/2024/05/lag1.jpg";"lag1"
```

## Performance og Skalering

### Database-optimalisering for store datamengder:

```sql
-- Legg til indekser for bedre ytelse
CREATE INDEX idx_nsk_diplom_type ON wp_posts(post_type, post_status);
CREATE INDEX idx_nsk_tildeling_dato ON wp_postmeta(meta_key, meta_value) WHERE meta_key = '_nsk_tildeling_dato';
CREATE INDEX idx_nsk_meta_keys ON wp_postmeta(meta_key) WHERE meta_key LIKE '_nsk_%';
```

### Caching-strategi:
- Bruk WordPress Transient API for cache av diplom-lister
- Implementer object caching for meta-data
- Cache bilde-URLs for raskere visning

### Paginering for store datasett:
```php
// Hent diplomer med paginering
$diplomer = get_posts(array(
    'post_type' => 'nsk_diplom',
    'posts_per_page' => 20,
    'paged' => $paged,
    'meta_key' => '_nsk_tildeling_dato',
    'orderby' => 'meta_value',
    'order' => 'DESC'
));
```

## Avanserte SQL Queries

### Finn diplomer uten bilder:
```sql
SELECT p.ID, p.post_title
FROM wp_posts p
LEFT JOIN wp_postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_nsk_diplom_bilde'
LEFT JOIN wp_postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_nsk_lag_bilde'
WHERE p.post_type = 'nsk_diplom'
    AND p.post_status = 'publish'
    AND (pm1.meta_value IS NULL OR pm1.meta_value = '')
    AND (pm2.meta_value IS NULL OR pm2.meta_value = '');
```

### Statistikk per år:
```sql
SELECT 
    YEAR(STR_TO_DATE(pm.meta_value, '%Y-%m-%d')) as aar,
    COUNT(*) as antall_diplomer
FROM wp_posts p
JOIN wp_postmeta pm ON p.ID = pm.post_id
WHERE p.post_type = 'nsk_diplom'
    AND p.post_status = 'publish'
    AND pm.meta_key = '_nsk_tildeling_dato'
    AND pm.meta_value != ''
GROUP BY aar
ORDER BY aar DESC;
```

### Finn duplikate diplomer:
```sql
SELECT post_title, COUNT(*) as antall
FROM wp_posts
WHERE post_type = 'nsk_diplom'
    AND post_status = 'publish'
GROUP BY post_title
HAVING COUNT(*) > 1;
```

## Vedlikehold og Monitoring

### Automatisk vedlikehold:
```php
// Registrer cleanup-jobb
if (!wp_next_scheduled('nsk_diplom_cleanup')) {
    wp_schedule_event(time(), 'weekly', 'nsk_diplom_cleanup');
}

add_action('nsk_diplom_cleanup', function() {
    // Slett orphaned attachments
    global $wpdb;
    
    $orphaned = $wpdb->get_results("
        SELECT p.ID
        FROM wp_posts p
        WHERE p.post_type = 'attachment'
            AND p.post_parent > 0
            AND p.post_parent NOT IN (
                SELECT ID FROM wp_posts WHERE post_type = 'nsk_diplom'
            )
    ");
    
    foreach ($orphaned as $attachment) {
        wp_delete_attachment($attachment->ID, true);
    }
});
```

### Health Check:
```sql
-- Sjekk data-integritet
SELECT 
    'Diplomer uten tildeling_dato' as sjekk,
    COUNT(*) as antall
FROM wp_posts p
LEFT JOIN wp_postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_nsk_tildeling_dato'
WHERE p.post_type = 'nsk_diplom'
    AND p.post_status = 'publish'
    AND (pm.meta_value IS NULL OR pm.meta_value = '')

UNION ALL

SELECT 
    'Diplomer uten forklarende tekst' as sjekk,
    COUNT(*) as antall
FROM wp_posts p
LEFT JOIN wp_postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_nsk_forklarende_tekst'
WHERE p.post_type = 'nsk_diplom'
    AND p.post_status = 'publish'
    AND (pm.meta_value IS NULL OR pm.meta_value = '')

UNION ALL

SELECT 
    'Diplomer uten diplom-bilde' as sjekk,
    COUNT(*) as antall
FROM wp_posts p
LEFT JOIN wp_postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_nsk_diplom_bilde'
WHERE p.post_type = 'nsk_diplom'
    AND p.post_status = 'publish'
    AND (pm.meta_value IS NULL OR pm.meta_value = '');
```

## Versjonering

Denne dokumentasjonen gjelder for:
- Plugin versjon: 1.1.0
- WordPress versjon: 5.0 - 6.8+
- Dato: Desember 2024

Ved fremtidige endringer i databasestrukturen, oppdater dette dokumentet tilsvarende.

## Referanser

- [WordPress Custom Post Types](https://developer.wordpress.org/plugins/post-types/)
- [WordPress Meta API](https://developer.wordpress.org/plugins/metadata/)
- [WordPress Attachment API](https://developer.wordpress.org/plugins/post-types/working-with-custom-post-types/#attachments)
- [WP-CLI Commands](https://wp-cli.org/commands/)
