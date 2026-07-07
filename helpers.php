<?php
/**
 * Render a static row of stars for a given average (supports halves).
 */
function render_stars(float $value, int $max = 5): string
{
    $out = '<span class="stars" aria-label="' . number_format($value, 1) . ' out of ' . $max . ' stars">';
    for ($i = 1; $i <= $max; $i++) {
        if ($value >= $i) {
            $out .= '<span class="star star-full">&#9733;</span>';
        } elseif ($value >= $i - 0.5) {
            $out .= '<span class="star star-half">&#9733;</span>';
        } else {
            $out .= '<span class="star star-empty">&#9734;</span>';
        }
    }
    $out .= '</span>';
    return $out;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
