#Requires -RunAsAdministrator
<#
    Minha Lista VIP — configuração do domínio local no WAMP
    -------------------------------------------------------
    1) adiciona "127.0.0.1 minhalistavip.com.br" ao arquivo hosts
    2) reinicia o Apache do WAMP para carregar o novo vhost
    3) testa http://minhalistavip.com.br/login

    O vhost já foi escrito em:
      C:\wamp64\bin\apache\apache2.4.62.1\conf\extra\httpd-vhosts.conf

    COMO EXECUTAR (PowerShell como Administrador):
      powershell -ExecutionPolicy Bypass -File "C:\wamp64\www\minhalistavip\tools\setup-vhost-admin.ps1"
#>

$ErrorActionPreference = 'Stop'

$dominio = 'minhalistavip.com.br'
$servico = 'wampapache64'
$hosts   = Join-Path $env:SystemRoot 'System32\drivers\etc\hosts'

Write-Host ''
Write-Host '=== Minha Lista VIP - dominio local ===' -ForegroundColor Cyan

# --- 1) Arquivo hosts -------------------------------------------------------
$conteudo = Get-Content -LiteralPath $hosts -Raw

if ($conteudo -match [regex]::Escape($dominio)) {
    Write-Host "[ok] '$dominio' ja consta no arquivo hosts."
} else {
    Add-Content -LiteralPath $hosts -Value "`r`n127.0.0.1`t$dominio`t# Minha Lista VIP (dev)" -Encoding ASCII
    Write-Host "[+] Entrada adicionada ao hosts: 127.0.0.1 $dominio"
}

# --- 2) Reiniciar o Apache --------------------------------------------------
Write-Host "[..] Reiniciando o Apache ($servico)..."
Restart-Service -Name $servico -Force
Start-Sleep -Seconds 3
Write-Host '[ok] Apache reiniciado.'

# --- 3) Teste ---------------------------------------------------------------
try {
    $resp = Invoke-WebRequest -Uri "http://$dominio/login" -UseBasicParsing -TimeoutSec 20
    Write-Host "[ok] http://$dominio/login respondeu HTTP $($resp.StatusCode)" -ForegroundColor Green
    Write-Host ''
    Write-Host "Tudo pronto! Acesse: http://$dominio/login" -ForegroundColor Green
} catch {
    Write-Host "[!] Nao foi possivel validar automaticamente: $($_.Exception.Message)" -ForegroundColor Yellow
    Write-Host "    Abra http://$dominio/login no navegador. Se falhar, reinicie os servicos pelo icone do WAMP."
}

Write-Host ''
Read-Host 'Pressione ENTER para fechar'
