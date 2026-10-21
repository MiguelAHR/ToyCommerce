$docxPath1 = "C:\Users\USUARIO\Documents\EVALUACION-E-BUSINESS-MINIKIDS-5-INTEGRANTES.docx"
$docxPath2 = "C:\Users\USUARIO\Documents\Admin\ToyCommerce\docs\EVALUACION-E-BUSINESS-MINIKIDS-5-INTEGRANTES.docx"

$word = New-Object -ComObject Word.Application
$word.Visible = $false
$word.DisplayAlerts = 0

try {
    $doc = $word.Documents.Open($docxPath1)
    Write-Host "Total InlineShapes: $($doc.InlineShapes.Count)"
    
    for ($i = 1; $i -le $doc.InlineShapes.Count; $i++) {
        $shape = $doc.InlineShapes.Item($i)
        if ($shape.LinkFormat) {
            $shape.LinkFormat.SavePictureWithDocument = $true
            $shape.LinkFormat.BreakLink()
            Write-Host "Incrustada imagen $i con exito."
        }
    }
    
    $doc.Save()
    $doc.SaveAs2($docxPath2, 16)
    $doc.Close()
    Write-Host "Documento guardado con imagenes incrustadas permanentemente."
}
finally {
    $word.Quit()
    [System.Runtime.Interopservices.Marshal]::ReleaseComObject($word) | Out-Null
}
