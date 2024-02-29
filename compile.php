<?php
function removeFilesNotInList($directory, $fileList) {
    // Получаем все файлы в директории
    $files = glob($directory . '/*');

    // Обходим все файлы и удаляем те, которые отсутствуют в списке
    foreach ($files as $file) {
        if (is_file($file) && !in_array(basename($file), $fileList)) {
            unlink($file);
        }
    }
}

function copyFilesBatch($baseDir, $targetDir, $fileTypes) {
    // Ассоциативный массив для хранения скопированных файлов
    $copiedFiles = [];

    // Обходим типы файлов
    foreach ($fileTypes as $fileType) {
        $sourceDir = $baseDir . '/' . $fileType;
        $destDir = $targetDir . '/' . $fileType;

        // Обходим файлы в исходной папке
        foreach (glob($sourceDir . '/*') as $file) {
            // Проверяем расширение файла
            $extension = pathinfo($file, PATHINFO_EXTENSION);

            // Пропускаем файлы с расширением .map
            if ($extension === 'map' || $extension === 'txt') {
                continue;
            }

            // Создаем запись для файла в ассоциативном массиве
            $copiedFiles[$fileType][] = basename($file);

            // Полный путь к целевому файлу
            $destPath = $destDir . '/' . basename($file);

            // Проверяем существование файла в целевой папке перед копированием
            if (!file_exists($destPath)) {
                // Копируем файл только если он не существует
                copy($file, $destPath);
            }
            foreach (glob("$sourceDir/main.*.$fileType") as $file2) {
                $mainFiles[$fileType] = basename($file2);
            }
        }
    }

    // Блок чтения списка файлов из files.json
    $filesJson = json_decode(file_get_contents('asset/asset-all.json'), true);
    
    // Блок удаления файлов, которые отличаются от тех, что записаны в files.json
    foreach ($fileTypes as $fileType) {
        $currentTargetDir = $targetDir . '/' . $fileType;
        if (isset($filesJson[$fileType])) {
            removeFilesNotInList($currentTargetDir, $filesJson[$fileType]);
        }
    }

    // Блок записи в файл JSON для всех файлов
    file_put_contents('asset/asset-manifest.json', json_encode(['file' => $mainFiles], JSON_PRETTY_PRINT));
    file_put_contents('asset/asset-all.json', json_encode($copiedFiles, JSON_PRETTY_PRINT));
}

// Указываем базовую директорию исходных файлов, целевую директорию и расширения файлов

$baseDirectory = 'react/build/static';//откуда извлекать файлы
$targetDirectory = 'static';//куда сувать файлы
$fileExtensions = ['js', 'css'];//допустимые типы

// Проверяем и создаем целевые директории, если они не существуют
if (!is_dir($targetDirectory . '/js')) {
    mkdir($targetDirectory . '/js', 0777, true);
}

if (!is_dir($targetDirectory . '/css')) {
    mkdir($targetDirectory . '/css', 0777, true);
}

// Вызываем функцию копирования
copyFilesBatch($baseDirectory, $targetDirectory, $fileExtensions);
?>