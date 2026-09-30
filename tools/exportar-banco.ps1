<#
    Exporta o banco local do WAMP (minhalistavip) para tools/db/minhalistavip-local.sql
    em UTF-8 **SEM BOM** — pronto para importar no phpMyAdmin da Hostinger.

    Uso:  powershell -ExecutionPolicy Bypass -File "C:\wamp64\www\minhalistavip\tools\exportar-banco.ps1"
#>

$ErrorActionPreference = 'Stop'

$mysqldump = (Get-ChildItem 'C:\wamp64\bin\mysql' -Recurse -Filter mysqldump.exe -ErrorAction SilentlyContinue |
    Select-Object -First 1).FullName

if (-not $mysqldump) {
    throw 'mysqldump.exe nao encontrado em C:\wamp64\bin\mysql'
}

$pasta   = Join-Path $PSScriptRoot 'db'
$destino = Join-Path $pasta 'minhalistavip-local.sql'
New-Item -ItemType Directory -Force -Path $pasta | Out-Null

Write-Host '[..] Exportando o banco minhalistavip...'
$linhas = & $mysqldump -u root --no-tablespaces --default-character-set=utf8mb4 --single-transaction minhalistavip 2>$null

if ($LASTEXITCODE -ne 0) {
    throw 'Falha ao executar o mysqldump.'
}

# Grava em UTF-8 SEM BOM (o BOM quebra a importacao)
$texto = ($linhas -join "`r`n") + "`r`n"
[System.IO.File]::WriteAllText($destino, $texto, (New-Object System.Text.UTF8Encoding($false)))

Write-Host "[ok] Dump gerado (UTF-8 sem BOM): $destino" -ForegroundColor Green
Write-Host ("     Tamanho: {0:N0} KB" -f ((Get-Item $destino).Length / 1KB))
