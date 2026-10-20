$projectRoot = Split-Path -Parent $PSScriptRoot
Set-Location -LiteralPath $projectRoot
& 'C:\xampp\php\php.exe' -S localhost:8082 -t $projectRoot (Join-Path $PSScriptRoot 'router-local.php')
