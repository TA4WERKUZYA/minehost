# /opt/minecraft-daemon/server_daemon.py
from aiohttp import web
import asyncio
import json
import os
import subprocess
import threading
import time
import shutil
from datetime import datetime
import logging
import aiofiles
import traceback
import zipfile

logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

class MinecraftDaemon:
    def __init__(self):
        self.servers_path = "/opt/minecraft-servers"
        self.cores_path = "/opt/minecraft-cores"
        self.active_servers = {}
        self.max_upload_size = 500 * 1024 * 1024  # 500 MB максимальный размер
        os.makedirs(self.servers_path, exist_ok=True)
        os.makedirs(self.cores_path, exist_ok=True)
    
    def find_core_for_server(self, game_type, data):
        """Найти подходящее ядро для сервера"""
        try:
            core_name = data.get('core_name', 'paper')
            core_version = data.get('core_version', '1.20.4')
            
            logger.info(f"Searching for core: {core_name} {core_version} (game_type: {game_type})")
            
            # Сначала проверяем точный путь
            exact_path = os.path.join(
                self.cores_path, 
                game_type, 
                core_name, 
                core_version, 
                f"{core_name}-{core_version}.jar"
            )
            
            logger.info(f"Checking exact path: {exact_path}")
            
            if os.path.exists(exact_path):
                logger.info(f"✅ Found exact core match: {exact_path}")
                return exact_path
            
            # Если не нашли по точному пути, ищем любой файл с таким именем в версии
            version_dir = os.path.join(self.cores_path, game_type, core_name, core_version)
            if os.path.exists(version_dir):
                for file in os.listdir(version_dir):
                    if file.endswith('.jar'):
                        file_path = os.path.join(version_dir, file)
                        logger.info(f"✅ Found JAR file in version dir: {file_path}")
                        return file_path
            
            # Если в точной версии нет, ищем последнюю версию этого ядра
            core_dir = os.path.join(self.cores_path, game_type, core_name)
            if os.path.exists(core_dir):
                versions = []
                for item in os.listdir(core_dir):
                    if os.path.isdir(os.path.join(core_dir, item)):
                        try:
                            # Пробуем распарсить версию
                            versions.append(item)
                        except:
                            continue
                
                if versions:
                    # Сортируем версии (простая сортировка)
                    versions.sort(key=lambda v: [int(x) for x in v.split('.') if x.isdigit()], reverse=True)
                    latest_version = versions[0]
                    latest_dir = os.path.join(core_dir, latest_version)
                    
                    # Ищем любой JAR файл в последней версии
                    if os.path.exists(latest_dir):
                        for file in os.listdir(latest_dir):
                            if file.endswith('.jar'):
                                latest_path = os.path.join(latest_dir, file)
                                logger.info(f"✅ Found latest version {latest_version}: {latest_path}")
                                return latest_path
            
            # Если ничего не нашли, ищем любой paper.jar в java директории
            if core_name == 'paper' and game_type == 'java':
                # Ищем в корне ядер
                for root, dirs, files in os.walk(self.cores_path):
                    for file in files:
                        if 'paper' in file.lower() and file.endswith('.jar'):
                            paper_path = os.path.join(root, file)
                            logger.info(f"✅ Found paper JAR: {paper_path}")
                            return paper_path
            
            logger.warning(f"❌ No core found for {game_type}/{core_name}")
            return None
            
        except Exception as e:
            logger.error(f"❌ Error finding core: {e}", exc_info=True)
            return None
    
    async def handle_create_server(self, request):
        """Обработчик создания сервера"""
        try:
            data = await request.json()
            logger.info(f"Received create request: {data}")
            
            server_id = data.get('server_id', 'unknown')
            server_name = data.get('server_name', f'server_{server_id}')
            port = data.get('port', 25565)
            memory = data.get('memory', 1024)
            game_type = data.get('game_type', 'java')
            
            # Запускаем создание сервера в отдельном потоке
            thread = threading.Thread(
                target=self.create_minecraft_server,
                args=(server_id, server_name, port, memory, game_type, data)
            )
            thread.daemon = True
            thread.start()
            
            response_data = {
                'success': True,
                'message': 'Server creation started',
                'server_id': server_id,
                'task_id': f'task_{server_id}',
                'port': port,
                'status': 'creating'
            }
            
            return web.json_response(response_data, status=202)
            
        except Exception as e:
            logger.error(f"Error creating server: {e}")
            return web.json_response({
                'success': False,
                'error': str(e)
            }, status=500)
    
    def create_minecraft_server(self, server_id, server_name, port, memory, game_type, data):
        """Создание Minecraft сервера"""
        try:
            server_dir = os.path.join(self.servers_path, str(server_id))
            os.makedirs(server_dir, exist_ok=True)
            
            logger.info(f"Creating server {server_name} (ID: {server_id}) in {server_dir}")
            
            # ВАЖНО: Получаем информацию о ядре из данных запроса
            core_name = data.get('core_name', 'paper')
            core_version = data.get('core_version', '1.20.4')
            
            logger.info(f"Looking for core: {core_name} {core_version} for game_type: {game_type}")
            
            # Ищем ядро в базе ядер
            core_path = self.find_core_for_server(game_type, data)
            
            if core_path and os.path.exists(core_path):
                # Копируем ядро в папку сервера
                target_core = os.path.join(server_dir, 'server.jar')
                shutil.copy2(core_path, target_core)
                logger.info(f"✅ Copied core from {core_path} to {target_core}")
                
                # Создаем eula.txt
                eula_path = os.path.join(server_dir, 'eula.txt')
                with open(eula_path, 'w') as f:
                    f.write("eula=true\n")
                
                logger.info(f"✅ Created eula.txt")
            else:
                logger.warning(f"❌ Core not found: {core_name}-{core_version}.jar")
                # Создаем файл-заглушку
                target_core = os.path.join(server_dir, 'server.jar')
                with open(target_core, 'w') as f:
                    f.write("# Placeholder - waiting for core installation\n")
                logger.info(f"Created placeholder server.jar")
            
            # Создаем server.properties
            properties = f"""#Minecraft server properties
server-port={port}
server-name={server_name}
max-players=20
online-mode=false
view-distance=10
motd={server_name}
gamemode=survival
enable-command-block=false
level-type=default
"""
            
            with open(os.path.join(server_dir, 'server.properties'), 'w') as f:
                f.write(properties)
            
            logger.info(f"✅ Created server.properties")
            
            # Создаем скрипт запуска
            start_script = f"""#!/bin/bash
cd "{server_dir}"
echo "=== Minecraft Server {server_name} ===" > server.log
echo "ID: {server_id}" >> server.log
echo "Port: {port}" >> server.log
echo "Memory: {memory}MB" >> server.log
echo "Started at: $(date)" >> server.log
echo "===============================" >> server.log

# Проверяем наличие server.jar
if [ -f "server.jar" ]; then
    jar_size=$(stat -f%z "server.jar" 2>/dev/null || stat -c%s "server.jar" 2>/dev/null)
    echo "Server jar size: ${{jar_size}} bytes" >> server.log
    
    if [ $jar_size -lt 1000 ]; then
        echo "WARNING: server.jar is too small or empty!" >> server.log
        echo "Server will wait for core installation..." >> server.log
        while true; do
            echo "[$(date)] Server {server_name} waiting for core..." >> server.log
            sleep 30
        done
    else
        echo "✅ Starting Minecraft server..." >> server.log
        java -Xms{memory}M -Xmx{memory}M -jar server.jar nogui >> server.log 2>&1
    fi
else
    echo "ERROR: server.jar not found!" >> server.log
    echo "Server will wait for core installation..." >> server.log
    while true; do
        echo "[$(date)] Server {server_name} waiting for core..." >> server.log
        sleep 30
    done
fi
"""
            
            script_path = os.path.join(server_dir, 'start.sh')
            with open(script_path, 'w') as f:
                f.write(start_script)
            
            os.chmod(script_path, 0o755)
            
            logger.info(f"✅ Created start.sh script")
            
            # Запускаем сервер
            process = subprocess.Popen(
                ['java', f'-Xms{memory}M', f'-Xmx{memory}M', '-jar', 'server.jar', 'nogui'],
                stdout=subprocess.PIPE,
                stderr=subprocess.PIPE,
                cwd=server_dir,
                text=True,  # Важно для чтения вывода
                bufsize=1,  # Построчная буферизация
                universal_newlines=True
            )
            
            self.active_servers[server_id] = {
                'process': process,
                'port': port,
                'started_at': datetime.now(),
                'dir': server_dir,
                'memory': memory,
                'game_type': game_type,
                'core_name': core_name,
                'core_version': core_version,
                'status': 'running'
            }
            
            logger.info(f"✅ Server {server_name} started successfully on port {port} (PID: {process.pid})")
            
            # Запускаем мониторинг вывода в отдельном потоке
            thread = threading.Thread(
                target=self.monitor_server_output,
                args=(server_id, process)
            )
            thread.daemon = True
            thread.start()
            
        except Exception as e:
            logger.error(f"❌ Failed to create server {server_id}: {e}", exc_info=True)
    
    def monitor_server_output(self, server_id, process):
        """Мониторинг вывода сервера"""
        try:
            logger.info(f"Starting output monitor for server {server_id}")
            
            # Читаем stdout
            for line in iter(process.stdout.readline, ''):
                if line:
                    logger.debug(f"Server {server_id}: {line.strip()}")
            
            # Когда процесс завершится
            process.wait()
            return_code = process.returncode
            
            logger.info(f"Server {server_id} process exited with code {return_code}")
            
            # Удаляем из активных серверов
            if server_id in self.active_servers:
                del self.active_servers[server_id]
                
        except Exception as e:
            logger.error(f"Error monitoring server {server_id}: {e}")
    
    async def handle_upload_core(self, request):
        """Загрузка ядра на ноду - multipart форма"""
        logger.info("=== MULTIPART UPLOAD HANDLER ===")
        
        try:
            # Проверяем авторизацию
            auth_header = request.headers.get('Authorization')
            if auth_header:
                logger.info(f"Auth header present")
            
            # Читаем multipart данные
            logger.info("Reading multipart data...")
            reader = await request.multipart()
            
            fields = {}
            
            async for field in reader:
                logger.info(f"Processing field: {field.name}")
                
                if field.name == 'file':
                    fields['file_field'] = field
                    fields['file_name'] = field.filename or 'unknown.jar'
                    
                else:
                    try:
                        data = await field.read()
                        if data:
                            fields[field.name] = data.decode('utf-8')
                    except Exception as e:
                        logger.error(f"Error reading field {field.name}: {e}")
            
            file_field = fields.get('file_field')
            file_name = fields.get('file_name') or fields.get('file_name', '')
            game_type = fields.get('game_type') or fields.get('game_type', '')
            core_name = fields.get('name') or fields.get('core_name') or fields.get('name', '')
            version = fields.get('version') or fields.get('version', '')
            
            if 'file_name' in fields and fields['file_name']:
                file_name = fields['file_name']
            
            logger.info(f"Extracted - game_type: {game_type}, core_name: {core_name}, version: {version}, file_name: {file_name}")
            
            if not all([file_field, game_type, core_name, version, file_name]):
                return web.json_response({
                    'success': False,
                    'error': f'Missing required fields'
                }, status=400)
            
            # Создаем директорию
            save_dir = os.path.join(self.cores_path, game_type, core_name, version)
            os.makedirs(save_dir, exist_ok=True)
            
            save_path = os.path.join(save_dir, file_name)
            temp_path = f"{save_path}.tmp"
            logger.info(f"Saving to: {save_path}")
            
            # Записываем файл
            async with aiofiles.open(temp_path, 'wb') as f:
                total_size = 0
                while True:
                    chunk = await file_field.read_chunk()
                    if not chunk:
                        break
                    
                    total_size += len(chunk)
                    await f.write(chunk)
                    
                    if total_size > self.max_upload_size:
                        await f.close()
                        os.remove(temp_path)
                        return web.json_response({
                            'success': False,
                            'error': f'File too large'
                        }, status=413)
            
            # Переименовываем временный файл
            if os.path.exists(temp_path):
                actual_size = os.path.getsize(temp_path)
                if actual_size == 0:
                    os.remove(temp_path)
                    return web.json_response({
                        'success': False,
                        'error': 'Received empty file'
                    }, status=400)
                
                if os.path.exists(save_path):
                    os.remove(save_path)
                
                shutil.move(temp_path, save_path)
                
                logger.info(f"File saved successfully: {actual_size} bytes")
                
                return web.json_response({
                    'success': True,
                    'message': 'Core uploaded successfully',
                    'path': save_path,
                    'size': actual_size,
                    'game_type': game_type,
                    'name': core_name,
                    'version': version,
                    'file_name': file_name
                })
            
        except Exception as e:
            logger.error(f"Multipart upload error: {e}", exc_info=True)
            return web.json_response({
                'success': False,
                'error': str(e)
            }, status=500)
    
    async def handle_upload_core_simple(self, request):
        """Простейшая загрузка - принимает raw файл в теле запроса (ИСПРАВЛЕНО)"""
        logger.info("=== SIMPLE UPLOAD HANDLER ===")
        
        try:
            # Проверяем авторизацию
            auth_header = request.headers.get('Authorization')
            if auth_header:
                logger.info(f"Auth header: {auth_header[:50]}...")
            
            # Параметры из query string
            game_type = request.query.get('game_type', 'java')
            core_name = request.query.get('name', 'unknown')
            version = request.query.get('version', '1.0')
            file_name = request.query.get('file_name', 'core.jar')
            
            logger.info(f"Params: game_type={game_type}, name={core_name}, version={version}, file_name={file_name}")
            
            # Проверяем размер файла
            content_length = request.content_length
            if content_length and content_length > self.max_upload_size:
                logger.error(f"File too large: {content_length} > {self.max_upload_size}")
                return web.json_response({
                    'success': False,
                    'error': f'File too large. Maximum size: {self.max_upload_size/1024/1024}MB'
                }, status=413)
            
            # Создаем директорию
            save_dir = os.path.join(self.cores_path, game_type, core_name, version)
            os.makedirs(save_dir, exist_ok=True)
            
            # Проверяем свободное место
            statvfs = os.statvfs(save_dir)
            free_space = statvfs.f_frsize * statvfs.f_bavail
            if content_length and content_length > free_space * 0.9:
                return web.json_response({
                    'success': False,
                    'error': f'Not enough disk space'
                }, status=507)
            
            save_path = os.path.join(save_dir, file_name)
            temp_path = f"{save_path}.tmp"
            logger.info(f"Saving to: {save_path}")
            
            # Используем aiofiles для асинхронной записи
            async with aiofiles.open(temp_path, 'wb') as f:
                total_read = 0
                chunk_count = 0
                
                # Читаем тело запроса по чанкам
                while True:
                    chunk = await request.content.read(64 * 1024)
                    if not chunk:
                        break
                    
                    total_read += len(chunk)
                    chunk_count += 1
                    
                    await f.write(chunk)
                    
                    if chunk_count % 160 == 0:
                        logger.info(f"Read {total_read/1024/1024:.2f} MB so far...")
                    
                    if total_read > self.max_upload_size:
                        logger.error(f"File exceeds max size during streaming")
                        await f.close()
                        os.remove(temp_path)
                        return web.json_response({
                            'success': False,
                            'error': f'File too large'
                        }, status=413)
            
            logger.info(f"File stream completed. Total size: {total_read} bytes ({total_read/1024/1024:.2f} MB)")
            
            # Переименовываем временный файл
            if os.path.exists(temp_path):
                actual_size = os.path.getsize(temp_path)
                if actual_size == 0:
                    os.remove(temp_path)
                    return web.json_response({
                        'success': False,
                        'error': 'Received empty file'
                    }, status=400)
                
                if os.path.exists(save_path):
                    os.remove(save_path)
                
                shutil.move(temp_path, save_path)
                
                # Проверяем JAR файл (опционально)
                if file_name.endswith('.jar'):
                    try:
                        with zipfile.ZipFile(save_path, 'r') as jar_file:
                            if 'META-INF/MANIFEST.MF' not in jar_file.namelist():
                                logger.warning(f"JAR file {file_name} might be invalid")
                    except zipfile.BadZipFile:
                        logger.warning(f"File {file_name} is not a valid ZIP/JAR file")
                
                logger.info(f"File saved successfully: {actual_size} bytes")
                
                return web.json_response({
                    'success': True,
                    'message': 'Core uploaded successfully',
                    'path': save_path,
                    'size': actual_size,
                    'game_type': game_type,
                    'name': core_name,
                    'version': version,
                    'file_name': file_name
                })
            else:
                logger.error("Temporary file was not created!")
                return web.json_response({
                    'success': False,
                    'error': 'File upload failed'
                }, status=500)
            
        except asyncio.CancelledError:
            logger.warning("Upload cancelled by client")
            raise
        except Exception as e:
            logger.error(f"Simple upload error: {e}", exc_info=True)
            if 'temp_path' in locals() and os.path.exists(temp_path):
                os.remove(temp_path)
            return web.json_response({
                'success': False,
                'error': str(e)
            }, status=500)
        
    async def handle_check_core_status(self, request):
        """Проверить статус ядра на сервера"""
        try:
            data = await request.json()
            server_id = data.get('server_id')
            server_path = data.get('server_path')
            
            if not server_path or not server_id:
                return web.json_response({
                    'success': False,
                    'error': 'Missing parameters'
                }, status=400)
            
            server_jar = os.path.join(server_path, 'server.jar')
            
            if os.path.exists(server_jar):
                stats = os.stat(server_jar)
                file_size = stats.st_size
                
                # Проверяем, не является ли файл пустым
                is_valid = file_size > 1000  # Более 1KB
                
                # Проверяем, является ли это JAR файлом
                is_jar = False
                try:
                    with zipfile.ZipFile(server_jar, 'r') as jar:
                        if 'META-INF/MANIFEST.MF' in jar.namelist():
                            is_jar = True
                except:
                    is_jar = False
                
                return web.json_response({
                    'success': True,
                    'core_installed': is_valid and is_jar,
                    'file_size': file_size,
                    'is_valid_jar': is_jar,
                    'path': server_jar,
                    'exists': True
                })
            else:
                return web.json_response({
                    'success': True,
                    'core_installed': False,
                    'exists': False,
                    'path': server_jar
                })
                
        except Exception as e:
            logger.error(f"Error checking core status: {e}", exc_info=True)
            return web.json_response({
                'success': False,
                'error': str(e)
            }, status=500)

    async def handle_download_core(self, request):
        """Скачать ядро с панели"""
        try:
            data = await request.json()
            core_id = data.get('core_id')
            download_url = data.get('download_url')
            
            if not download_url:
                return web.json_response({
                    'success': False,
                    'error': 'No download URL provided'
                }, status=400)
            
            logger.info(f"Downloading core from: {download_url}")
            
            # Создаем временный файл
            import tempfile
            temp_file = tempfile.NamedTemporaryFile(delete=False, suffix='.jar')
            temp_path = temp_file.name
            temp_file.close()
            
            # Скачиваем файл
            import aiohttp
            async with aiohttp.ClientSession() as session:
                async with session.get(download_url) as response:
                    if response.status != 200:
                        return web.json_response({
                            'success': False,
                            'error': f'Download failed: {response.status}'
                        }, status=response.status)
                    
                    total_size = 0
                    async with aiofiles.open(temp_path, 'wb') as f:
                        async for chunk in response.content.iter_chunked(8192):
                            await f.write(chunk)
                            total_size += len(chunk)
                    
                    logger.info(f"Downloaded {total_size} bytes to {temp_path}")
                    
                    # Проверяем файл
                    if total_size < 1000:
                        os.remove(temp_path)
                        return web.json_response({
                            'success': False,
                            'error': 'File too small, likely invalid'
                        }, status=400)
                    
                    # Перемещаем в нужную директорию
                    game_type = data.get('game_type', 'java')
                    core_name = data.get('core_name', 'paper')
                    version = data.get('version', '1.20.4')
                    file_name = data.get('file_name', f'{core_name}-{version}.jar')
                    
                    save_dir = os.path.join(self.cores_path, game_type, core_name, version)
                    os.makedirs(save_dir, exist_ok=True)
                    
                    save_path = os.path.join(save_dir, file_name)
                    
                    if os.path.exists(save_path):
                        os.remove(save_path)
                    
                    shutil.move(temp_path, save_path)
                    
                    return web.json_response({
                        'success': True,
                        'message': 'Core downloaded successfully',
                        'path': save_path,
                        'size': total_size,
                        'file_name': file_name
                    })
                    
        except Exception as e:
            logger.error(f"Error downloading core: {e}", exc_info=True)
            if 'temp_path' in locals() and os.path.exists(temp_path):
                os.remove(temp_path)
            return web.json_response({
                'success': False,
                'error': str(e)
            }, status=500)    

    async def handle_install_core(self, request):
        """Установка ядра на сервер"""
        logger.info("=== INSTALL CORE HANDLER ===")
        
        try:
            data = await request.json()
            logger.info(f"Install core request: {data}")
            
            server_id = data.get('server_id')
            core_name = data.get('core_name')
            core_version = data.get('core_version')
            game_type = data.get('game_type')
            file_name = data.get('file_name')
            server_path = data.get('server_path')
            
            if not all([server_id, core_name, core_version, file_name, server_path]):
                return web.json_response({
                    'success': False,
                    'error': 'Missing required parameters'
                }, status=400)
            
            # Путь к ядру на ноде
            core_path = os.path.join(
                self.cores_path,
                game_type,
                core_name,
                core_version,
                file_name
            )
            
            if not os.path.exists(core_path):
                logger.error(f"Core not found: {core_path}")
                return web.json_response({
                    'success': False,
                    'error': f'Core not found: {file_name}'
                }, status=404)
            
            # Создаем папку сервера если нет
            os.makedirs(server_path, exist_ok=True)
            
            # Копируем ядро в папку сервера
            target_path = os.path.join(server_path, 'server.jar')
            
            # Удаляем старое ядро если есть
            if os.path.exists(target_path):
                # Создаем бэкап старого ядра
                backup_path = os.path.join(server_path, 'server.jar.backup')
                shutil.move(target_path, backup_path)
                logger.info(f"Backup created: {backup_path}")
            
            # Копируем новое ядро
            shutil.copy2(core_path, target_path)
            
            logger.info(f"Core installed: {core_path} -> {target_path}")
            
            return web.json_response({
                'success': True,
                'message': 'Core installed successfully',
                'core_path': core_path,
                'server_path': server_path,
                'target_path': target_path
            })
            
        except Exception as e:
            logger.error(f"Error installing core: {e}", exc_info=True)
            return web.json_response({
                'success': False,
                'error': str(e)
            }, status=500)
    
    async def handle_list_cores(self, request):
        """Список доступных ядер"""
        try:
            cores = []
            
            if not os.path.exists(self.cores_path):
                logger.warning(f"Cores directory does not exist: {self.cores_path}")
                return web.json_response({
                    'success': True,
                    'cores': [],
                    'total': 0,
                    'cores_path': self.cores_path
                })
            
            for game_type in os.listdir(self.cores_path):
                game_type_path = os.path.join(self.cores_path, game_type)
                if not os.path.isdir(game_type_path):
                    continue
                
                for core_name in os.listdir(game_type_path):
                    core_path = os.path.join(game_type_path, core_name)
                    if not os.path.isdir(core_path):
                        continue
                    
                    for version in os.listdir(core_path):
                        version_path = os.path.join(core_path, version)
                        if not os.path.isdir(version_path):
                            continue
                        
                        for file in os.listdir(version_path):
                            if file.endswith('.jar'):
                                full_path = os.path.join(version_path, file)
                                try:
                                    stats = os.stat(full_path)
                                    cores.append({
                                        'game_type': game_type,
                                        'name': core_name,
                                        'version': version,
                                        'file_name': file,
                                        'path': full_path,
                                        'size': stats.st_size,
                                        'modified': datetime.fromtimestamp(stats.st_mtime).isoformat()
                                    })
                                except Exception as e:
                                    logger.error(f"Error reading core {full_path}: {e}")
            
            return web.json_response({
                'success': True,
                'cores': cores,
                'total': len(cores),
                'cores_path': self.cores_path
            })
            
        except Exception as e:
            logger.error(f"Error listing cores: {e}")
            return web.json_response({
                'success': False,
                'error': str(e)
            }, status=500)
    
    async def handle_delete_core(self, request):
        """Удаление ядра"""
        try:
            data = await request.json()
            core_path = data.get('path')
            
            if not core_path or not os.path.exists(core_path):
                return web.json_response({
                    'success': False,
                    'error': 'Core not found'
                }, status=404)
            
            if not core_path.startswith(self.cores_path):
                return web.json_response({
                    'success': False,
                    'error': 'Access denied'
                }, status=403)
            
            os.remove(core_path)
            
            # Удаляем пустые директории
            dir_path = os.path.dirname(core_path)
            while dir_path.startswith(self.cores_path):
                try:
                    os.rmdir(dir_path)
                    dir_path = os.path.dirname(dir_path)
                except OSError:
                    break
            
            logger.info(f"Core deleted: {core_path}")
            
            return web.json_response({
                'success': True,
                'message': 'Core deleted successfully'
            })
            
        except Exception as e:
            logger.error(f"Error deleting core: {e}")
            return web.json_response({
                'success': False,
                'error': str(e)
            }, status=500)
    
    async def handle_health(self, request):
        """Health check endpoint"""
        core_count = 0
        try:
            for root, dirs, files in os.walk(self.cores_path):
                for file in files:
                    if file.endswith('.jar'):
                        core_count += 1
        except:
            pass
        
        return web.json_response({
            'status': 'healthy',
            'timestamp': datetime.now().isoformat(),
            'active_servers': len(self.active_servers),
            'available_cores': core_count,
            'servers_path': self.servers_path,
            'cores_path': self.cores_path,
            'max_upload_size_mb': self.max_upload_size / 1024 / 1024
        })
    
    async def handle_server_status(self, request):
        """Проверка статуса сервера (работает он или нет)"""
        try:
            data = await request.json()
            server_id = data.get('server_id')
            
            if not server_id:
                return web.json_response({
                    'success': False,
                    'error': 'Missing server_id'
                }, status=400)
            
            server_dir = os.path.join(self.servers_path, str(server_id))
            
            # 1. Проверяем, знает ли демон об этом сервере
            if server_id in self.active_servers:
                process = self.active_servers[server_id]['process']
                
                # Проверяем, жив ли процесс
                if process.poll() is None:
                    return web.json_response({
                        'success': True,
                        'server_id': server_id,
                        'status': 'running',
                        'managed_by_daemon': True,
                        'pid': process.pid,
                        'port': self.active_servers[server_id]['port'],
                        'started_at': self.active_servers[server_id]['started_at'].isoformat(),
                        'uptime_seconds': (datetime.now() - self.active_servers[server_id]['started_at']).total_seconds()
                    })
                else:
                    # Процесс завершился
                    del self.active_servers[server_id]
            
            # 2. Ищем процесс Java на порту сервера
            try:
                # Ищем процесс Java, слушающий нужный порт
                port = 25565  # Получите порт из server.properties
                
                # Читаем порт из конфига
                config_path = os.path.join(server_dir, 'server.properties')
                if os.path.exists(config_path):
                    with open(config_path, 'r') as f:
                        for line in f:
                            if line.startswith('server-port='):
                                try:
                                    port = int(line.split('=')[1].strip())
                                except:
                                    pass
                
                # Ищем процесс по порту
                cmd = f"lsof -ti:{port}"
                result = subprocess.run(cmd, shell=True, capture_output=True, text=True)
                
                if result.returncode == 0 and result.stdout.strip():
                    pid = int(result.stdout.strip().split()[0])
                    
                    # Получаем время работы
                    etime_cmd = f"ps -p {pid} -o etime="
                    etime_result = subprocess.run(etime_cmd, shell=True, capture_output=True, text=True)
                    uptime = etime_result.stdout.strip()
                    
                    return web.json_response({
                        'success': True,
                        'server_id': server_id,
                        'status': 'running',
                        'managed_by_daemon': False,
                        'pid': pid,
                        'port': port,
                        'uptime': uptime,
                        'note': 'Found by port scan'
                    })
                    
            except Exception as e:
                logger.warning(f"Error checking port {port}: {e}")
            
            # 3. Сервер не запущен
            return web.json_response({
                'success': True,
                'server_id': server_id,
                'status': 'stopped',
                'managed_by_daemon': False,
                'pid': None,
                'port': None
            })
            
        except Exception as e:
            logger.error(f"Error checking server status: {e}", exc_info=True)
            return web.json_response({
                'success': False,
                'error': str(e)
            }, status=500)
    
    async def handle_list_servers(self, request):
        """Список всех серверов с реальными статусами"""
        try:
            servers_info = {}
            
            # 1. Сначала добавляем серверы, управляемые демоном
            for server_id, info in self.active_servers.items():
                alive = info['process'].poll() is None
                servers_info[server_id] = {
                    'port': info['port'],
                    'started_at': info['started_at'].isoformat(),
                    'dir': info['dir'],
                    'memory': info['memory'],
                    'game_type': info['game_type'],
                    'status': 'running' if alive else 'stopped',
                    'managed_by_daemon': True,
                    'pid': info['process'].pid if alive else None
                }
            
            # 2. Ищем другие серверы в папке servers_path
            if os.path.exists(self.servers_path):
                for server_dir_name in os.listdir(self.servers_path):
                    server_dir = os.path.join(self.servers_path, server_dir_name)
                    
                    # Пропускаем, если это не директория или уже в списке
                    if not os.path.isdir(server_dir) or server_dir_name in servers_info:
                        continue
                    
                    # Проверяем, есть ли процесс для этого сервера
                    is_running = False
                    pid = None
                    port = 25565
                    
                    try:
                        # Ищем процесс Java, работающий в этой директории
                        ps_cmd = ['pgrep', '-f', f'java.*{server_dir}']
                        result = subprocess.run(ps_cmd, capture_output=True, text=True)
                        
                        if result.returncode == 0:
                            pids = result.stdout.strip().split()
                            if pids:
                                is_running = True
                                pid = int(pids[0])
                                
                                # Пытаемся получить порт
                                config_path = os.path.join(server_dir, 'server.properties')
                                if os.path.exists(config_path):
                                    with open(config_path, 'r') as f:
                                        for line in f:
                                            if line.startswith('server-port='):
                                                try:
                                                    port = int(line.split('=')[1].strip())
                                                except:
                                                    pass
                    except:
                        pass
                    
                    servers_info[server_dir_name] = {
                        'port': port,
                        'dir': server_dir,
                        'status': 'running' if is_running else 'stopped',
                        'managed_by_daemon': False,
                        'pid': pid,
                        'memory': 1024,  # По умолчанию
                        'game_type': 'java'  # По умолчанию
                    }
            
            return web.json_response({
                'success': True,
                'servers': servers_info,
                'total': len(servers_info)
            })
            
        except Exception as e:
            logger.error(f"Error listing servers: {e}", exc_info=True)
            return web.json_response({
                'success': False,
                'error': str(e)
            }, status=500)
    
    async def handle_start_server(self, request):
        """Запуск существующего сервера"""
        try:
            data = await request.json()
            server_id = data.get('server_id')
            
            if not server_id:
                return web.json_response({
                    'success': False,
                    'error': 'Missing server_id'
                }, status=400)
            
            # Проверяем, запущен ли уже сервер
            if server_id in self.active_servers:
                process = self.active_servers[server_id]['process']
                if process.poll() is None:  # Процесс еще жив
                    return web.json_response({
                        'success': False,
                        'error': f'Server {server_id} is already running',
                        'pid': process.pid
                    }, status=409)
            
            # Ищем сервер по ID
            server_dir = os.path.join(self.servers_path, str(server_id))
            if not os.path.exists(server_dir):
                return web.json_response({
                    'success': False,
                    'error': f'Server directory not found: {server_dir}'
                }, status=404)
            
            # Проверяем наличие скрипта запуска
            start_script = os.path.join(server_dir, 'start.sh')
            if not os.path.exists(start_script):
                return web.json_response({
                    'success': False,
                    'error': f'Start script not found: {start_script}'
                }, status=404)
            
            # Запускаем сервер
            process = subprocess.Popen(
                ['/bin/bash', start_script],
                stdout=subprocess.PIPE,
                stderr=subprocess.PIPE,
                cwd=server_dir
            )
            
            # Читаем конфигурацию сервера
            config_path = os.path.join(server_dir, 'server.properties')
            port = 25565
            memory = 1024
            game_type = 'java'
            
            if os.path.exists(config_path):
                with open(config_path, 'r') as f:
                    for line in f:
                        if line.startswith('server-port='):
                            try:
                                port = int(line.split('=')[1].strip())
                            except:
                                pass
            
            self.active_servers[server_id] = {
                'process': process,
                'port': port,
                'started_at': datetime.now(),
                'dir': server_dir,
                'memory': memory,
                'game_type': game_type
            }
            
            logger.info(f"✅ Server {server_id} started successfully (PID: {process.pid})")
            
            return web.json_response({
                'success': True,
                'message': f'Server {server_id} started',
                'pid': process.pid,
                'port': port,
                'server_id': server_id
            })
            
        except Exception as e:
            logger.error(f"Error starting server: {e}", exc_info=True)
            return web.json_response({
                'success': False,
                'error': str(e)
            }, status=500)
    
    async def handle_restart_server(self, request):
        """Перезапуск сервера"""
        try:
            data = await request.json()
            server_id = data.get('server_id')
            
            if not server_id:
                return web.json_response({
                    'success': False,
                    'error': 'Missing server_id'
                }, status=400)
            
            # Останавливаем, если запущен
            if server_id in self.active_servers:
                process = self.active_servers[server_id]['process']
                if process.poll() is None:  # Процесс жив
                    process.terminate()
                    try:
                        process.wait(timeout=10)
                    except subprocess.TimeoutExpired:
                        process.kill()
                    del self.active_servers[server_id]
                    logger.info(f"Stopped server {server_id} before restart")
            
            # Ждем немного перед запуском
            await asyncio.sleep(2)
            
            # Запускаем сервер (повторно используем логику handle_start_server)
            server_dir = os.path.join(self.servers_path, str(server_id))
            start_script = os.path.join(server_dir, 'start.sh')
            
            if not os.path.exists(start_script):
                return web.json_response({
                    'success': False,
                    'error': f'Start script not found'
                }, status=404)
            
            process = subprocess.Popen(
                ['/bin/bash', start_script],
                stdout=subprocess.PIPE,
                stderr=subprocess.PIPE,
                cwd=server_dir
            )
            
            # Читаем конфигурацию
            config_path = os.path.join(server_dir, 'server.properties')
            port = 25565
            if os.path.exists(config_path):
                with open(config_path, 'r') as f:
                    for line in f:
                        if line.startswith('server-port='):
                            try:
                                port = int(line.split('=')[1].strip())
                            except:
                                pass
            
            self.active_servers[server_id] = {
                'process': process,
                'port': port,
                'started_at': datetime.now(),
                'dir': server_dir,
                'memory': 1024,
                'game_type': 'java'
            }
            
            logger.info(f"✅ Server {server_id} restarted (PID: {process.pid})")
            
            return web.json_response({
                'success': True,
                'message': f'Server {server_id} restarted',
                'pid': process.pid,
                'port': port
            })
            
        except Exception as e:
            logger.error(f"Error restarting server: {e}", exc_info=True)
            return web.json_response({
                'success': False,
                'error': str(e)
            }, status=500)
    
    async def handle_stop_server(self, request):
        """Остановка сервера"""
        try:
            data = await request.json()
            server_id = data.get('server_id')
            
            if server_id in self.active_servers:
                process = self.active_servers[server_id]['process']
                process.terminate()
                process.wait(timeout=5)
                del self.active_servers[server_id]
                
                return web.json_response({
                    'success': True,
                    'message': f'Server {server_id} stopped'
                })
            else:
                return web.json_response({
                    'success': False,
                    'error': f'Server {server_id} not found'
                }, status=404)
                
        except Exception as e:
            return web.json_response({
                'success': False,
                'error': str(e)
            }, status=500)

async def create_app():
    """Создание приложения aiohttp с увеличенным лимитом"""
    daemon = MinecraftDaemon()
    
    app = web.Application(client_max_size=daemon.max_upload_size)
    
    # Регистрируем все роуты
    app.router.add_post('/api/create-server', daemon.handle_create_server)
    app.router.add_get('/api/health', daemon.handle_health)
    app.router.add_get('/api/servers', daemon.handle_list_servers)
    app.router.add_post('/api/stop-server', daemon.handle_stop_server)
    
    # Новые роуты для управления серверами
    app.router.add_post('/api/start-server', daemon.handle_start_server)
    app.router.add_post('/api/restart-server', daemon.handle_restart_server)
    app.router.add_post('/api/server-status', daemon.handle_server_status)
    
    app.router.add_post('/api/core/upload-simple', daemon.handle_upload_core_simple)
    app.router.add_post('/api/core/upload', daemon.handle_upload_core)
    app.router.add_get('/api/cores', daemon.handle_list_cores)
    app.router.add_delete('/api/core/delete', daemon.handle_delete_core)
    app.router.add_post('/api/core/delete', daemon.handle_delete_core)  # Альтернатива для DELETE
    app.router.add_post('/api/install-core', daemon.handle_install_core)
    
    # Корневой путь
    app.router.add_get('/', lambda request: web.json_response({
        'service': 'Minecraft Server Daemon',
        'version': '2.0',
        'max_upload_size': f"{daemon.max_upload_size/1024/1024:.1f}MB",
        'features': ['server_management', 'core_management'],
        'endpoints': [
            'POST /api/create-server',
            'GET /api/health',
            'GET /api/servers',
            'POST /api/start-server',
            'POST /api/stop-server',
            'POST /api/restart-server',
            'POST /api/server-status',
            'POST /api/core/upload',
            'POST /api/core/upload-simple',
            'GET /api/cores',
            'DELETE /api/core/delete',
            'POST /api/install-core'
        ]
    }))
    
    return app

if __name__ == '__main__':
    logger.info("Starting Minecraft Server Daemon v2.0 (Fixed Version)...")
    logger.info(f"Maximum upload size: {500}MB")
    
    web.run_app(
        create_app(), 
        host='0.0.0.0', 
        port=8080,
        access_log=logger
    )