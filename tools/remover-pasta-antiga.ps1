<#
    Minha Lista VIP — limpeza da pasta antiga do projeto
    ---------------------------------------------------
    A pasta C:\wamp64\www\listas_eventos ficou vazia após a renomeação para
    minhalistavip, mas não pôde ser apagada na hora porque o terminal que
    hospeda o OpenCode estava aberto nela (o Windows bloqueia renomear/apagar
    um diretório que é o diretório de trabalho de um processo).

    Este script é executado automaticamente no próximo logon por um atalho na
    pasta Inicializar ("MinhaListaVIP-LimparPastaAntiga.lnk"), que ele próprio
    remove ao final.

    Para rodar manualmente (num terminal que NÃO esteja na pasta antiga):
      powershell -ExecutionPolicy Bypass -File "C:\wamp64\www\minhalistavip\tools\remover-pasta-antiga.ps1"
#>

$ErrorActionPreference = 'Continue'

$alvo    = 'C:\wamp64\www\listas_eventos'
$log     = 'C:\wamp64\www\minhalistavip\writable\limpeza-pasta-antiga.log'
$atalho  = Join-Path ([Environment]::GetFolderPath('Startup')) 'MinhaListaVIP-LimparPastaAntiga.lnk'

function Gravar([string] $mensagem) {
    $linha = "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')  $mensagem"
    Add-Content -LiteralPath $log -Value $linha -ErrorAction SilentlyContinue
}

if (Test-Path -LiteralPath $alvo) {
    $removido = $false

    for ($tentativa = 1; $tentativa -le 5 -and -not $removido; $tentativa++) {
        try {
            Remove-Item -LiteralPath $alvo -Recurse -Force -ErrorAction Stop
            $removido = $true
        } catch {
            Start-Sleep -Seconds 3
        }
    }

    Gravar $(if ($removido) { "Pasta antiga removida: $alvo" } else { "Ainda em uso, nao removida: $alvo" })
} else {
    Gravar "Pasta antiga ja nao existia: $alvo"
}

# Cumpriu o objetivo: remove o atalho da pasta Inicializar.
if (Test-Path -LiteralPath $atalho) {
    try {
        Remove-Item -LiteralPath $atalho -Force -ErrorAction Stop
        Gravar "Atalho da pasta Inicializar removido."
    } catch {
        Gravar "Nao foi possivel remover o atalho: $($_.Exception.Message)"
    }
}
