<?php
/**
 * Sam's Fruit Wines - User Dashboard Shared Navbar
 * Reuses the exact original floating pill navbar & slide-out overlay from header.html.
 */

$headerFile = __DIR__ . '/../header.html';
if (file_exists($headerFile)) {
    $rawHeader = file_get_contents($headerFile);

    // Adjust relative links and image paths for the user/ subdirectory
    $replacements = [
        'href="index.html"' => 'href="../index.html"',
        'href="shop.html"' => 'href="../shop.html"',
        'href="about.html"' => 'href="../about.html"',
        'href="visit.html"' => 'href="../visit.html"',
        'href="ourretailers.html"' => 'href="../ourretailers.html"',
        'href="ourreatailers.html"' => 'href="../ourretailers.html"',
        'href="contact.html"' => 'href="../contact.html"',
        'href="signup.html#wineclub"' => 'href="../signup.html#wineclub"',
        'href="signup.html"' => 'href="../signup.html"',
        'href="cart.html"' => 'href="../cart.html"',
        'href="privacy.html"' => 'href="../privacy.html"',
        'href="terms.html"' => 'href="../terms.html"',
        'href="user/dashboard.php"' => 'href="dashboard.php"',
        'src="images/' => 'src="../images/',
        'data-img="images/' => 'data-img="../images/',
        'src="mail_handler.js"' => 'src="../mail_handler.js"',
    ];
    $headerAdapted = str_replace(array_keys($replacements), array_values($replacements), $rawHeader);

    // Extract style block
    if (preg_match('/<style\b[^>]*>([\s\S]*?)<\/style>/i', $headerAdapted, $mStyle)) {
        echo "<style>\n" . $mStyle[1] . "\n</style>\n";
    }

    // Extract navbar block
    if (preg_match('/<nav\b[^>]*class="navbar"[\s\S]*?<\/nav>/i', $headerAdapted, $mNav)) {
        echo $mNav[0] . "\n";
    }

    // Extract menu-overlay block
    $posOverlay = strpos($headerAdapted, '<div class="menu-overlay">');
    if ($posOverlay !== false) {
        $posScript = strpos($headerAdapted, '<script>', $posOverlay);
        if ($posScript !== false) {
            $overlayHtml = substr($headerAdapted, $posOverlay, $posScript - $posOverlay);
            echo $overlayHtml . "\n";
        }
    }

    // Extract script block
    if (preg_match('/<script\b[^>]*>([\s\S]*?)<\/script>/i', $headerAdapted, $mScript)) {
        echo "<script>\n" . $mScript[1] . "\n</script>\n";
    }
}
