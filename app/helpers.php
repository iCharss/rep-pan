<?php
function asset($path) {
    $publicPath = __DIR__ . '/../public/' . ltrim($path, '/');
    return '/public/' . $path . (file_exists($publicPath) ? '?v=' . filemtime($publicPath) : '');
}

function renderWithAssets($view, $data = []) {
    $data['css'] = asset('styles.css');
    $data['js'] = asset('script.js');
    Flight::render($view, $data);
}