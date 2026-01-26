<?php
$oldNamespace = 'CustomFields\\LaravelCustomFields';
$newNamespace = 'Salah\\LaravelCustomFields';

$exclude = ['vendor', '.git', 'node_modules', '.phpunit.cache'];

function updateInDir($dirPath, $old, $new, $exclude) {
    if (!is_dir($dirPath)) return;
    
    $files = scandir($dirPath);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        if (in_array($file, $exclude)) continue;
        
        $path = $dirPath . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            updateInDir($path, $old, $new, $exclude);
        } else {
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            if (in_array($ext, ['php', 'json', 'md', 'xml'])) {
                $content = file_get_contents($path);
                if (str_contains($content, $old)) {
                    $newContent = str_replace($old, $new, $content);
                    file_put_contents($path, $newContent);
                    echo "Updated: $path\n";
                }
            }
        }
    }
}

updateInDir(__DIR__, $oldNamespace, $newNamespace, $exclude);
echo "Done.\n";
