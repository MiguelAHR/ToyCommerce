$ErrorActionPreference = 'Stop'
if (netstat -ano -p tcp | Select-String ':8082\s+\S+\s+LISTENING') { throw '8082 is occupied. Stop only its verified listener before starting Apache.' }
$projectRoot = Split-Path -Parent $PSScriptRoot
$privateDir = Join-Path (Split-Path -Parent $projectRoot) 'local-private'
$configFile = Join-Path $privateDir 'apache-local-v5.conf'
$template = Get-Content -Raw -LiteralPath (Join-Path $PSScriptRoot 'apache-local-v5.conf.in')
$config = $template.Replace('@ROOT@', $projectRoot.Replace('\','/')).Replace('@PRIVATE@', $privateDir.Replace('\','/'))
if (Test-Path -LiteralPath $configFile) {
    Copy-Item -LiteralPath $configFile -Destination ($configFile + '.bak-' + (Get-Date -Format 'yyyyMMdd-HHmmss'))
}
[System.IO.File]::WriteAllText($configFile, $config, [System.Text.UTF8Encoding]::new($false))
& 'C:\xampp\apache\bin\httpd.exe' -t -f $configFile
if ($LASTEXITCODE -ne 0) { throw 'Invalid Apache configuration' }
# Stop only the verified process listening on 8082 before invoking this script.
Start-Process -FilePath 'C:\xampp\apache\bin\httpd.exe' -ArgumentList @('-f', ('"' + $configFile + '"')) -WorkingDirectory $projectRoot -WindowStyle Hidden
