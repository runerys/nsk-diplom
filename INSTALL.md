# Installasjonsinstruksjon for NSK Diplom Plugin

## Steg 1: Forbered filene

1. **Pakk plugin-filene i en ZIP-fil:**
   - Høyreklikk på `nsk-diplom` mappen
   - Velg "Send to" > "Compressed (zipped) folder"
   - Gi ZIP-filen navnet `nsk-diplom-plugin.zip`

## Steg 2: Last opp til WordPress

### Alternativ A: Via WordPress Admin (Anbefalt)

1. **Logg inn i WordPress admin**
   - Gå til din WordPress-sides admin område (vanligvis `yoursite.com/wp-admin`)

2. **Gå til Plugins**
   - Klikk på "Plugins" i venstremenyen
   - Klikk på "Add New"

3. **Last opp plugin**
   - Klikk på "Upload Plugin" øverst på siden
   - Klikk "Choose File" og velg `nsk-diplom-plugin.zip`
   - Klikk "Install Now"

4. **Aktiver pluginet**
   - Etter installasjon, klikk "Activate Plugin"

### Alternativ B: Via FTP/cPanel

1. **Pakk ut ZIP-filen lokalt**
2. **Last opp via FTP:**
   - Koble til din server via FTP
   - Naviger til `/wp-content/plugins/`
   - Last opp hele `nsk-diplom` mappen
3. **Aktiver i WordPress admin:**
   - Gå til "Plugins" i WordPress admin
   - Finn "NSK Diplom Plugin" og klikk "Activate"

## Steg 3: Opprett nødvendige sider

### Diplomer-siden (Offentlig)

1. **Opprett ny side:**
   - Gå til "Pages" > "Add New" i WordPress admin
   - Tittel: "Diplomer" 
   - Innhold: `[nsk_diplomer]`
   - Permalink: Endre til `/diplomer/` (under "Permalink" seksjonen)
   - Publiser siden

### Legg til diplom-siden (Kun for innloggede)

1. **Opprett ny side:**
   - Gå til "Pages" > "Add New" i WordPress admin
   - Tittel: "Legg til diplom"
   - Innhold: `[nsk_diplom_form]`
   - Permalink: Endre til `/legg-til-diplom/` (under "Permalink" seksjonen)
   - Publiser siden

## Steg 4: Legg til navigasjon (Valgfritt)

1. **Legg til i hovedmenyen:**
   - Gå til "Appearance" > "Menus"
   - Velg din hovedmeny
   - Legg til "Diplomer"-siden i menyen
   - Klikk "Save Menu"

## Steg 5: Test funksjonen

### Test som besøkende:
1. Gå til `/diplomer/` på din side
2. Du skal se en tom liste (eller eksisterende diplomer)
3. Hvis du er innlogget, skal du se "Legg til nytt diplom" knapper

### Test som innlogget bruker:
1. Logg inn på WordPress
2. Gå til `/legg-til-diplom/`
3. Fyll ut skjemaet og test opplasting
4. Sjekk at diplomen vises på diplomer-siden

### Test som administrator:
1. Gå til "Diplomer" i WordPress admin venstremenyen
2. Du skal se alle diplomer og kunne administrere dem
3. Test å legge til et nytt diplom via admin

## Feilsøking

### Pluginet vises ikke i plugin-listen:
- Sjekk at alle filene er lastet opp riktig
- Sjekk at `index.php` filen er i rot-mappen

### Shortcodes fungerer ikke:
- Sjekk at pluginet er aktivert
- Sjekk at shortcode er skrevet riktig: `[nsk_diplomer]` og `[nsk_diplom_form]`

### Bildeopplasting fungerer ikke:
- Sjekk at WordPress har skriverettigheter til `/wp-content/uploads/`
- Sjekk at max upload size er stor nok (kan endres i PHP-innstillinger)

### 404-feil på diploma-sider:
- Gå til "Settings" > "Permalinks" i WordPress admin
- Klikk "Save Changes" for å oppdatere rewrite rules

## Tilleggsinnstillinger

### Brukertilganger:
- Som standard kan alle innloggede brukere legge til diplomer
- For å begrense tilgang, kan du installere et plugin som "User Role Editor"

### Backup:
- Anbefaler å ta backup før installasjon
- Pluginet bruker WordPress' egne tabeller, så ingen ekstra databaser

## Support

Ved problemer:
1. Sjekk WordPress error logs
2. Kontakt NSK sin webansvarlige
3. Sjekk plugin-filene for eventuelle PHP-feil

Pluginet er testet med WordPress 6.8 og PHP 7.2+.

## WordPress 6.8 Kompatibilitet

Dette pluginet er fullt kompatibelt med WordPress 6.8 og inkluderer:
- ✅ REST API støtte
- ✅ Block Editor (Gutenberg) kompatibilitet  
- ✅ Moderne WordPress hooks og filters
- ✅ Optimaliserte admin scripts
- ✅ PHP 7.2+ støtte (anbefaler 7.4 eller nyere)
