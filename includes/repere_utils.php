<?php
// Utilitaires de codification des repères

function classifyRepere(string $repere): string
{
    $r = strtoupper(trim($repere));
    if ($r === '') return 'Inconnu';

    // Règles spécifiques multi-caractères d'abord
    if (str_starts_with($r, 'LFD') || str_starts_with($r, 'LFD+S') || preg_match('/^LFD\+?S/i', $r)) return 'Filtres Détendeurs';
    if (str_starts_with($r, 'MP')) return 'Moteurs';
    if (str_starts_with($r, 'SV')) return 'Soupapes';
    if (str_starts_with($r, 'LG')) return 'Niveaux à glace';
    if (str_starts_with($r, 'FE')) return 'Plaques à orifice';
    if (str_starts_with($r, 'EXT')) return 'Extincteurs';

    // V en troisième position = vannes auto (ex: XXV...) => index 2 (0-based)
    if (strlen($r) >= 3 && $r[2] === 'V') return 'Vannes automatiques';

    // Monocaractère au début
    switch ($r[0]) {
        case 'K':
            return 'Colonnes';
        case 'E':
            return 'Aéro / Échangeurs';
        case 'D':
            return 'Ballons';
        case 'T':
            return 'Réservoirs / Bacs';
        case 'F':
            return 'Fours';
        case 'R':
            return 'Réacteurs';
        case 'C':
            return 'Compresseurs';
        case 'P':
            return 'Pompes';
        default:
            return 'Autres';
    }
}
