$word = New-Object -ComObject Word.Application
$word.Visible = $false
Write-Host "Word application created successfully. Version: $($word.Version)"
$word.Quit()
[System.Runtime.Interopservices.Marshal]::ReleaseComObject($word) | Out-Null
