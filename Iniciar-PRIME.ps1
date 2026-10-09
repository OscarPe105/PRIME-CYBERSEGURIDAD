$ErrorActionPreference = 'Stop'
$primeProjectRoot = $PSScriptRoot
$primeBackend = Join-Path $primeProjectRoot 'backend'
$primePortablePhp = [IO.Path]::GetFullPath((Join-Path $primeProjectRoot '../../work/runtime/php/php.exe'))
$primePhpCommand = Get-Command php -ErrorAction SilentlyContinue
if (Test-Path -LiteralPath $primePortablePhp) { $primePhp = $primePortablePhp }
elseif ($primePhpCommand) { $primePhp = $primePhpCommand.Source }
else { throw 'PHP no está disponible. Consulta README.md para preparar PHP 8.4 y las dependencias.' }
if (!(Test-Path -LiteralPath (Join-Path $primeBackend 'vendor/autoload.php'))) { throw 'Faltan dependencias Laravel. Ejecuta composer install según README.md.' }
if (!(Test-Path -LiteralPath (Join-Path $primeBackend '.env'))) { throw 'Configura .env y la base de datos según README.md.' }
$primeClient = [Net.Sockets.TcpClient]::new()
try { $primeClient.Connect('127.0.0.1',8000); Write-Output 'El puerto 8000 ya está ocupado. Si es PRIME, abre http://127.0.0.1:8000; en otro caso, detén ese servicio antes de iniciar.'; return }
catch { }
finally { $primeClient.Dispose() }
$primeLogDir = Join-Path $primeProjectRoot '.local'
New-Item -ItemType Directory -Path $primeLogDir -Force | Out-Null
$primeProcess = Start-Process -FilePath $primePhp -ArgumentList @('artisan','serve','--host=127.0.0.1','--port=8000','--no-ansi') -WorkingDirectory $primeBackend -WindowStyle Hidden -RedirectStandardOutput (Join-Path $primeLogDir 'server.log') -RedirectStandardError (Join-Path $primeLogDir 'server-error.log') -PassThru
$primeProcess.Id | Set-Content -LiteralPath (Join-Path $primeLogDir 'server.pid')
Write-Output 'PRIME se está iniciando en http://127.0.0.1:8000. El proceso permanece oculto; los registros se guardan en .local.'
