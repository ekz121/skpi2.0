param(
    [string]$Output = "skpi-hostinger-tanpa-ssh.zip"
)

$ErrorActionPreference = 'Stop'

$projectDir = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$vendorAutoload = Join-Path $projectDir 'vendor\autoload.php'
$databaseExport = Join-Path $projectDir 'database\skpi-hostinger.sql'
$databaseUpdate = Join-Path $projectDir 'database\update-2026-10-14.sql'
$publicIndex = Join-Path $PSScriptRoot 'hostinger-no-ssh\public_html\index.php'
$guide = Join-Path $projectDir 'docs\PANDUAN-HOSTINGER.md'
$outputPath = Join-Path $projectDir $Output

foreach ($requiredFile in @($vendorAutoload, $databaseExport, $databaseUpdate, $publicIndex, $guide)) {
    if (-not (Test-Path -LiteralPath $requiredFile -PathType Leaf)) {
        throw "File wajib tidak ditemukan: $requiredFile"
    }
}

$tempRoot = [IO.Path]::GetFullPath([IO.Path]::GetTempPath())
$buildDir = Join-Path $tempRoot ('skpi-hostinger-build-' + [guid]::NewGuid().ToString('N'))
$stageDir = Join-Path $buildDir 'stage'

New-Item -ItemType Directory -Path $stageDir -Force | Out-Null

try {
    $applicationDir = Join-Path $stageDir 'skpi_app'
    New-Item -ItemType Directory -Path $applicationDir -Force | Out-Null

    $projectFiles = & git -C $projectDir ls-files --cached --others --exclude-standard
    if ($LASTEXITCODE -ne 0) {
        throw 'Daftar file proyek gagal dibaca.'
    }

    foreach ($relativePath in $projectFiles) {
        $normalizedPath = $relativePath.Replace('\', '/')
        if ($normalizedPath -match '^(vendor|node_modules|tests|storage/logs)/' -or $normalizedPath -match '\.zip$') {
            continue
        }
        $sourcePath = Join-Path $projectDir $relativePath
        if (-not (Test-Path -LiteralPath $sourcePath -PathType Leaf)) {
            continue
        }
        $destinationPath = Join-Path $applicationDir $relativePath
        $destinationDirectory = Split-Path -Parent $destinationPath
        New-Item -ItemType Directory -Path $destinationDirectory -Force | Out-Null
        Copy-Item -LiteralPath $sourcePath -Destination $destinationPath -Force
    }
    Copy-Item -LiteralPath (Join-Path $projectDir 'vendor') -Destination (Join-Path $applicationDir 'vendor') -Recurse -Force

    $publicHtmlDir = Join-Path $stageDir 'public_html'
    Copy-Item -LiteralPath (Join-Path $applicationDir 'public') -Destination $publicHtmlDir -Recurse -Force
    Copy-Item -LiteralPath $publicIndex -Destination (Join-Path $publicHtmlDir 'index.php') -Force

    $keyBytes = New-Object byte[] 32
    $generator = [Security.Cryptography.RandomNumberGenerator]::Create()
    try {
        $generator.GetBytes($keyBytes)
    } finally {
        $generator.Dispose()
    }
    $applicationKey = 'base64:' + [Convert]::ToBase64String($keyBytes)

    $environment = @"
APP_NAME="SKEM dan SKPI Polteksi"
APP_ENV=production
APP_KEY=$applicationKey
APP_DEBUG=false
APP_URL=https://skpi.polteksi.ac.id
APP_TIMEZONE=Asia/Jakarta

APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID

APP_MAINTENANCE_DRIVER=file
BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u841207907_skpi
DB_USERNAME=u841207907_skpi
DB_PASSWORD="GANTI_PASSWORD_DATABASE"

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_SCHEME=smtps
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=GANTI_EMAIL_HOSTINGER
MAIL_PASSWORD="GANTI_PASSWORD_EMAIL"
MAIL_FROM_ADDRESS=GANTI_EMAIL_HOSTINGER
MAIL_FROM_NAME="SKEM dan SKPI Polteksi"

VITE_APP_NAME="SKEM dan SKPI Polteksi"

SKPI_DEMO_MODE=false
SKPI_SIGNATORY_NAME="GANTI_NAMA_PEJABAT"
SKPI_SIGNATORY_NIDN="GANTI_NIDN_PEJABAT"
SKPI_INSTITUTION_ACCREDITATION=""
"@
    $utf8WithoutBom = New-Object Text.UTF8Encoding($false)
    [IO.File]::WriteAllText((Join-Path $applicationDir '.env'), $environment, $utf8WithoutBom)

    $databaseDir = Join-Path $stageDir 'database-import'
    New-Item -ItemType Directory -Path $databaseDir -Force | Out-Null
    Copy-Item -LiteralPath $databaseExport -Destination (Join-Path $databaseDir 'skpi-hostinger.sql') -Force
    Copy-Item -LiteralPath $databaseUpdate -Destination (Join-Path $databaseDir 'update-2026-10-14.sql') -Force
    Copy-Item -LiteralPath $guide -Destination (Join-Path $stageDir 'PANDUAN-HOSTINGER.md') -Force

    if (Test-Path -LiteralPath $outputPath) {
        Remove-Item -LiteralPath $outputPath -Force
    }

    Add-Type -AssemblyName System.IO.Compression
    Add-Type -AssemblyName System.IO.Compression.FileSystem
    $stream = [IO.File]::Open($outputPath, [IO.FileMode]::CreateNew, [IO.FileAccess]::ReadWrite)
    try {
        $archive = New-Object IO.Compression.ZipArchive($stream, [IO.Compression.ZipArchiveMode]::Create, $false)
        try {
            Get-ChildItem -LiteralPath $stageDir -Recurse -File | ForEach-Object {
                $entryName = $_.FullName.Substring($stageDir.Length + 1).Replace('\', '/')
                [IO.Compression.ZipFileExtensions]::CreateEntryFromFile(
                    $archive,
                    $_.FullName,
                    $entryName,
                    [IO.Compression.CompressionLevel]::Optimal
                ) | Out-Null
            }
        } finally {
            $archive.Dispose()
        }
    } finally {
        $stream.Dispose()
    }

    Get-Item -LiteralPath $outputPath | Select-Object FullName, Length, LastWriteTime
} finally {
    $resolvedBuildDir = [IO.Path]::GetFullPath($buildDir)
    if ($resolvedBuildDir.StartsWith($tempRoot, [StringComparison]::OrdinalIgnoreCase) -and (Test-Path -LiteralPath $resolvedBuildDir)) {
        Remove-Item -LiteralPath $resolvedBuildDir -Recurse -Force
    }
}
