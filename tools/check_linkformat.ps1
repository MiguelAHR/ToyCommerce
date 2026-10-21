$docxPath1 = "C:\Users\USUARIO\Documents\EVALUACION-E-BUSINESS-MINIKIDS-5-INTEGRANTES.docx"
$word = New-Object -ComObject Word.Application
$word.Visible = $false
try {
    $doc = $word.Documents.Open($docxPath1)
    $shape = $doc.InlineShapes.Item(1)
    if ($shape.LinkFormat) {
        $shape.LinkFormat | Get-Member | Select-Object Name, MemberType
    }
    $doc.Close()
}
finally {
    $word.Quit()
    [System.Runtime.Interopservices.Marshal]::ReleaseComObject($word) | Out-Null
}
