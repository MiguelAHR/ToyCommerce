$htmlPath = "C:\Users\USUARIO\Documents\Admin\ToyCommerce\docs\evaluacion_print.html"
$docxPath1 = "C:\Users\USUARIO\Documents\EVALUACION-E-BUSINESS-MINIKIDS-5-INTEGRANTES.docx"
$docxPath2 = "C:\Users\USUARIO\Documents\Admin\ToyCommerce\docs\EVALUACION-E-BUSINESS-MINIKIDS-5-INTEGRANTES.docx"

Write-Host "Iniciando Word COM..."
$word = New-Object -ComObject Word.Application
$word.Visible = $false
$word.DisplayAlerts = 0

try {
    Write-Host "Abriendo archivo HTML: $htmlPath"
    $doc = $word.Documents.Open($htmlPath, $false, $true)

    # 16 = wdFormatXMLDocument (.docx)
    Write-Host "Guardando en $docxPath1..."
    $doc.SaveAs2($docxPath1, 16)

    Write-Host "Guardando en $docxPath2..."
    $doc.SaveAs2($docxPath2, 16)

    $doc.Close()
    Write-Host "Conversion completada con exito."
}
catch {
    Write-Error "Error durante la conversion: $_"
}
finally {
    $word.Quit()
    [System.Runtime.Interopservices.Marshal]::ReleaseComObject($word) | Out-Null
    [System.GC]::Collect()
    [System.GC]::WaitForPendingFinalizers()
}
