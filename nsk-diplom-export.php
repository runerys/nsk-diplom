<?php
/**
 * NSK Diplom Data Export Script
 * 
 * Dette scriptet kan brukes til å eksportere diplom-data til ulike formater
 * for backup eller migrering til andre systemer.
 * 
 * BRUK: Legg denne filen i WordPress rot-katalog og kjør via browser eller CLI
 * Eksempel: http://yoursite.com/nsk-diplom-export.php?format=csv&key=YOUR_SECRET_KEY
 */

// Sikkerhet: Sett et hemmelig nøkkel for å forhindre uautorisert tilgang
define('EXPORT_SECRET_KEY', 'nsk_diplom_export_2024_secret_key_change_this');

// WordPress bootstrap
require_once('wp-config.php');
require_once('wp-load.php');

/**
 * NSK Diplom Data Exporter Class
 */
class NSK_Diplom_Exporter {
    
    private $allowed_formats = ['csv', 'json', 'xml', 'sql'];
    
    public function __construct() {
        // Sjekk sikkerhetsnøkkel
        if (!isset($_GET['key']) || $_GET['key'] !== EXPORT_SECRET_KEY) {
            wp_die('Unauthorized access. Invalid security key.');
        }
        
        // Sjekk format
        $format = isset($_GET['format']) ? sanitize_text_field($_GET['format']) : 'csv';
        if (!in_array($format, $this->allowed_formats)) {
            wp_die('Invalid format. Allowed: ' . implode(', ', $this->allowed_formats));
        }
        
        $this->export_data($format);
    }
    
    /**
     * Hent alle diplom-data fra databasen
     */
    private function get_diplom_data() {
        global $wpdb;
        
        $query = "
            SELECT 
                d.ID as diplom_id,
                d.post_title as diplom_tittel,
                d.post_date as opprettet_dato,
                d.post_author as opprettet_av_id,
                u.display_name as opprettet_av_navn,
                u.user_login as opprettet_av_bruker,
                d.post_status,
                pm1.meta_value as tildeling_dato,
                pm2.meta_value as forklarende_tekst,
                pm3.meta_value as diplom_bilde_id,
                pm4.meta_value as lag_bilde_id,
                diplom_img.guid as diplom_bilde_url,
                diplom_img.post_title as diplom_bilde_navn,
                lag_img.guid as lag_bilde_url,
                lag_img.post_title as lag_bilde_navn
            FROM {$wpdb->posts} d
            LEFT JOIN {$wpdb->users} u ON d.post_author = u.ID
            LEFT JOIN {$wpdb->postmeta} pm1 ON d.ID = pm1.post_id AND pm1.meta_key = '_nsk_tildeling_dato'
            LEFT JOIN {$wpdb->postmeta} pm2 ON d.ID = pm2.post_id AND pm2.meta_key = '_nsk_forklarende_tekst'
            LEFT JOIN {$wpdb->postmeta} pm3 ON d.ID = pm3.post_id AND pm3.meta_key = '_nsk_diplom_bilde'
            LEFT JOIN {$wpdb->postmeta} pm4 ON d.ID = pm4.post_id AND pm4.meta_key = '_nsk_lag_bilde'
            LEFT JOIN {$wpdb->posts} diplom_img ON pm3.meta_value = diplom_img.ID
            LEFT JOIN {$wpdb->posts} lag_img ON pm4.meta_value = lag_img.ID
            WHERE d.post_type = 'nsk_diplom'
                AND d.post_status = 'publish'
            ORDER BY pm1.meta_value DESC
        ";
        
        return $wpdb->get_results($query, ARRAY_A);
    }
    
    /**
     * Eksporter data i spesifisert format
     */
    private function export_data($format) {
        $data = $this->get_diplom_data();
        $filename = 'nsk_diplomer_' . date('Y-m-d_H-i-s');
        
        switch ($format) {
            case 'csv':
                $this->export_csv($data, $filename);
                break;
            case 'json':
                $this->export_json($data, $filename);
                break;
            case 'xml':
                $this->export_xml($data, $filename);
                break;
            case 'sql':
                $this->export_sql($data, $filename);
                break;
        }
    }
    
    /**
     * Eksporter til CSV format
     */
    private function export_csv($data, $filename) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // UTF-8 BOM for riktig encoding i Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Header
        $headers = [
            'ID',
            'Tittel',
            'Tildeling Dato',
            'Forklarende Tekst',
            'Opprettet Dato',
            'Opprettet Av',
            'Diplom Bilde URL',
            'Diplom Bilde Navn',
            'Lag Bilde URL',
            'Lag Bilde Navn',
            'Status'
        ];
        fputcsv($output, $headers, ';');
        
        // Data rows
        foreach ($data as $row) {
            $csv_row = [
                $row['diplom_id'],
                $row['diplom_tittel'],
                $row['tildeling_dato'],
                $row['forklarende_tekst'],
                $row['opprettet_dato'],
                $row['opprettet_av_navn'] . ' (' . $row['opprettet_av_bruker'] . ')',
                $row['diplom_bilde_url'],
                $row['diplom_bilde_navn'],
                $row['lag_bilde_url'],
                $row['lag_bilde_navn'],
                $row['post_status']
            ];
            fputcsv($output, $csv_row, ';');
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Eksporter til JSON format
     */
    private function export_json($data, $filename) {
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.json"');
        
        $export_data = [
            'export_info' => [
                'plugin' => 'NSK Diplom Plugin',
                'version' => '1.1.0',
                'export_date' => current_time('c'),
                'total_diplomer' => count($data),
                'wordpress_version' => get_bloginfo('version'),
                'site_url' => get_site_url()
            ],
            'diplomer' => []
        ];
        
        foreach ($data as $row) {
            $diplom = [
                'id' => (int)$row['diplom_id'],
                'tittel' => $row['diplom_tittel'],
                'tildeling_dato' => $row['tildeling_dato'],
                'forklarende_tekst' => $row['forklarende_tekst'],
                'opprettet_dato' => $row['opprettet_dato'],
                'opprettet_av' => [
                    'id' => (int)$row['opprettet_av_id'],
                    'navn' => $row['opprettet_av_navn'],
                    'bruker' => $row['opprettet_av_bruker']
                ],
                'diplom_bilde' => $row['diplom_bilde_id'] ? [
                    'id' => (int)$row['diplom_bilde_id'],
                    'url' => $row['diplom_bilde_url'],
                    'navn' => $row['diplom_bilde_navn']
                ] : null,
                'lag_bilde' => $row['lag_bilde_id'] ? [
                    'id' => (int)$row['lag_bilde_id'],
                    'url' => $row['lag_bilde_url'],
                    'navn' => $row['lag_bilde_navn']
                ] : null,
                'status' => $row['post_status']
            ];
            
            $export_data['diplomer'][] = $diplom;
        }
        
        echo json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    /**
     * Eksporter til XML format
     */
    private function export_xml($data, $filename) {
        header('Content-Type: application/xml; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.xml"');
        
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><nsk_diplomer></nsk_diplomer>');
        
        // Export info
        $export_info = $xml->addChild('export_info');
        $export_info->addChild('plugin', 'NSK Diplom Plugin');
        $export_info->addChild('version', '1.1.0');
        $export_info->addChild('export_date', current_time('c'));
        $export_info->addChild('total_diplomer', count($data));
        $export_info->addChild('wordpress_version', get_bloginfo('version'));
        $export_info->addChild('site_url', htmlspecialchars(get_site_url()));
        
        // Diplomer
        $diplomer = $xml->addChild('diplomer');
        
        foreach ($data as $row) {
            $diplom = $diplomer->addChild('diplom');
            $diplom->addAttribute('id', $row['diplom_id']);
            
            $diplom->addChild('tittel', htmlspecialchars($row['diplom_tittel']));
            $diplom->addChild('tildeling_dato', $row['tildeling_dato']);
            $diplom->addChild('forklarende_tekst', htmlspecialchars($row['forklarende_tekst']));
            $diplom->addChild('opprettet_dato', $row['opprettet_dato']);
            
            $opprettet_av = $diplom->addChild('opprettet_av');
            $opprettet_av->addChild('id', $row['opprettet_av_id']);
            $opprettet_av->addChild('navn', htmlspecialchars($row['opprettet_av_navn']));
            $opprettet_av->addChild('bruker', htmlspecialchars($row['opprettet_av_bruker']));
            
            if ($row['diplom_bilde_id']) {
                $diplom_bilde = $diplom->addChild('diplom_bilde');
                $diplom_bilde->addChild('id', $row['diplom_bilde_id']);
                $diplom_bilde->addChild('url', htmlspecialchars($row['diplom_bilde_url']));
                $diplom_bilde->addChild('navn', htmlspecialchars($row['diplom_bilde_navn']));
            }
            
            if ($row['lag_bilde_id']) {
                $lag_bilde = $diplom->addChild('lag_bilde');
                $lag_bilde->addChild('id', $row['lag_bilde_id']);
                $lag_bilde->addChild('url', htmlspecialchars($row['lag_bilde_url']));
                $lag_bilde->addChild('navn', htmlspecialchars($row['lag_bilde_navn']));
            }
            
            $diplom->addChild('status', $row['post_status']);
        }
        
        echo $xml->asXML();
        exit;
    }
    
    /**
     * Eksporter til SQL format
     */
    private function export_sql($data, $filename) {
        global $wpdb;
        
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.sql"');
        
        echo "-- NSK Diplom Plugin Data Export\n";
        echo "-- Generated: " . current_time('Y-m-d H:i:s') . "\n";
        echo "-- WordPress Version: " . get_bloginfo('version') . "\n";
        echo "-- Plugin Version: 1.1.0\n";
        echo "-- Total Diplomer: " . count($data) . "\n\n";
        
        echo "-- Create temporary tables for import\n";
        echo "CREATE TABLE IF NOT EXISTS temp_nsk_diplom_posts (\n";
        echo "  ID int(11) NOT NULL,\n";
        echo "  post_title text NOT NULL,\n";
        echo "  post_date datetime NOT NULL,\n";
        echo "  post_author bigint(20) NOT NULL,\n";
        echo "  post_status varchar(20) NOT NULL DEFAULT 'publish',\n";
        echo "  post_type varchar(20) NOT NULL DEFAULT 'nsk_diplom'\n";
        echo ");\n\n";
        
        echo "CREATE TABLE IF NOT EXISTS temp_nsk_diplom_meta (\n";
        echo "  post_id int(11) NOT NULL,\n";
        echo "  meta_key varchar(255) NOT NULL,\n";
        echo "  meta_value longtext\n";
        echo ");\n\n";
        
        echo "-- Insert diplom posts\n";
        foreach ($data as $row) {
            $post_title = $wpdb->prepare('%s', $row['diplom_tittel']);
            $post_date = $wpdb->prepare('%s', $row['opprettet_dato']);
            $post_author = (int)$row['opprettet_av_id'];
            $post_status = $wpdb->prepare('%s', $row['post_status']);
            
            echo "INSERT INTO temp_nsk_diplom_posts (ID, post_title, post_date, post_author, post_status, post_type) VALUES\n";
            echo "({$row['diplom_id']}, {$post_title}, {$post_date}, {$post_author}, {$post_status}, 'nsk_diplom');\n";
            
            // Insert metadata
            if ($row['tildeling_dato']) {
                $meta_value = $wpdb->prepare('%s', $row['tildeling_dato']);
                echo "INSERT INTO temp_nsk_diplom_meta (post_id, meta_key, meta_value) VALUES ({$row['diplom_id']}, '_nsk_tildeling_dato', {$meta_value});\n";
            }
            
            if ($row['forklarende_tekst']) {
                $meta_value = $wpdb->prepare('%s', $row['forklarende_tekst']);
                echo "INSERT INTO temp_nsk_diplom_meta (post_id, meta_key, meta_value) VALUES ({$row['diplom_id']}, '_nsk_forklarende_tekst', {$meta_value});\n";
            }
            
            if ($row['diplom_bilde_id']) {
                echo "INSERT INTO temp_nsk_diplom_meta (post_id, meta_key, meta_value) VALUES ({$row['diplom_id']}, '_nsk_diplom_bilde', '{$row['diplom_bilde_id']}');\n";
            }
            
            if ($row['lag_bilde_id']) {
                echo "INSERT INTO temp_nsk_diplom_meta (post_id, meta_key, meta_value) VALUES ({$row['diplom_id']}, '_nsk_lag_bilde', '{$row['lag_bilde_id']}');\n";
            }
            
            echo "\n";
        }
        
        echo "-- End of export\n";
        exit;
    }
}

// Kjør eksport hvis script kalles direkte
if (basename($_SERVER['PHP_SELF']) === 'nsk-diplom-export.php') {
    new NSK_Diplom_Exporter();
}
