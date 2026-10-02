<#
    Exporta o banco local do WAMP (minhalistavip) para tools/db/minhalistavip-local.sql
    em UTF-8 **SEM BOM** — pronto para importar no phpMyAdmin da Hostinger.

    Uso:  powershell -ExecutionPolicy Bypass -File "C:\wamp64\www\minhalistavip\tools\exportar-banco.ps1"

    IMPORTANTE: usamos `--result-file`, que faz o próprio mysqldump gravar os bytes no
    arquivo. Capturar a saída com `& mysqldump ...` e depois gravar com PowerShell faz o
    terminal decodificar os bytes UTF-8 usando a codepage do console (CP850/CP437) e
    recodificar em UTF-8, gerando MOJIBAKE nos acentos (ex.: "João" -> "Jo├úo").
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

if (Test-Path $destino) {
    Remove-Item $destino -Force
}

Write-Host '[..] Exportando o banco minhalistavip...'

# `--result-file` grava os bytes diretamente no arquivo (sem passar pela decodificação
# de texto do PowerShell), preservando o UTF-8 dos acentos.
& $mysqldump -u root --no-tablespaces --default-character-set=utf8mb4 --single-transaction "--result-file=$destino" minhalistavip

if ($LASTEXITCODE -ne 0) {
    throw 'Falha ao executar o mysqldump.'
}

Write-Host "[ok] Dump gerado (UTF-8 sem BOM): $destino" -ForegroundColor Green
Write-Host ("     Tamanho: {0:N0} KB" -f ((Get-Item $destino).Length / 1KB))
