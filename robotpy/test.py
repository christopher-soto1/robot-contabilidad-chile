#INICIO BANCO SANTANDER
#chrome driver, para banco santander
#import undetected_chromedriver as uc

#playground
#from playwright.sync_api import sync_playwright, TimeoutError
#import random
from openpyxl import load_workbook
from openpyxl import Workbook

#FIN BANCO SANTANDER

#INICIO BANCO CHILE
import unittest
from selenium import webdriver
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.action_chains import ActionChains
from selenium.webdriver.support.ui import Select
from selenium.common.exceptions import TimeoutException
import os
import shutil
import time
import requests
from datetime import datetime, timedelta
import json
import logging
import re
import pandas as pd
import numpy as np
import csv
#import PyPDF2
import sys
import io

#FORMATEO DE XLS -> XLSX
import xlrd
import openpyxl
from datetime import datetime

import requests

#----------------------------------------------------# CHILE #----------------------------------------------------#

sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
timeout_sesion = 10
#directorio_raiz = r'C:\Users\programadorll\Desktop\robotpy\documentos' #Ruta antigua antes de la unificacion
directorio_raiz = r'C:\xampp\htdocs\robot-contabilidad-chile\robotpy\documentos'

def obtener_credencialesDeAcceso(usuario):
    try:
        # Obtener la ruta del directorio actual (donde se encuentra el script Python)
        # directorio_actual = os.path.dirname(os.path.abspath(__file__))
        directorio_actual = r'C:\xampp\htdocs\robot-contabilidad-chile\robotpy'
        # Ruta al archivo JSON
        ruta_archivo = os.path.join(directorio_actual, 'usuarios.json')

        with open(ruta_archivo, 'r', encoding='utf-8') as archivo:
            datos = json.load(archivo)

            # Imprimir los datos leídos (opcional, puedes eliminarlo si no necesitas ver los datos)
            # print(datos)

            # Obtener la lista de usuarios
            usuario_bd_chile = datos.get(usuario, [])

            if usuario_bd_chile:
                # Extraer los valores de cada objeto dentro de la lista
                credenciales = []  # Lista para almacenar las credenciales
                for usuario in usuario_bd_chile:
                    usuarioB = usuario['usuario']
                    contrasena = usuario['pass']
                    url = usuario['url']
                    # Guardar las credenciales en el formato que desees (como un diccionario)
                    credenciales.append({
                        'usuario': usuarioB,
                        'contrasena': contrasena,
                        'url': url,
                        'directorio_actual': directorio_actual
                    })
                return credenciales
            else:
                print("No se encontraron datos para 'usuarioBdChile'.")
                return None  # Si no se encontraron usuarios, devolver None
            
    except FileNotFoundError:
        print(f"El archivo {ruta_archivo} no fue encontrado obtener_credencialesDeAcceso.")
        return None
    except json.JSONDecodeError:
        print(f"El archivo {ruta_archivo} no es un archivo JSON válido obtener_credencialesDeAcceso.")
        return None
    except Exception as e:
        #log_exception(f"Error obtener_credencialesDeAcceso: {e}")  # Registrar la excepción en el archivo de log
        print(f"Ocurrió un error: {e}")
        return None

def banco_chile(usuario, timeout_sesion):
    driver = None
    tiempo_espera = timeout_sesion
    try:
        cuenta_corriente = '00-178-02933-05'
        credenciales = obtener_credencialesDeAcceso(usuario)
        if credenciales:
            fecha_hoy = datetime.now().strftime("%d-%m-%Y")
            usuarioB = credenciales[0]['usuario']
            contrasena = credenciales[0]['contrasena']
            url = credenciales[0]['url']
            #url = "http://10.255.255.1"
            directorio_actual = credenciales[0]['directorio_actual']
            # Carpeta raíz del día actual
            carpeta_dia = os.path.join(directorio_actual, 'documentos', 'banco_chile', fecha_hoy)

            
            download_dir = os.path.join(carpeta_dia, '33-05')
            os.makedirs(download_dir, exist_ok=True)  # Crea la carpeta si no existe

            ruta_archivo_descarga = os.path.join(download_dir, "CartolaEmitida.xls")
            if os.path.exists(ruta_archivo_descarga):
                os.remove(ruta_archivo_descarga)

            ruta_pdf = os.path.join(download_dir, "CartolaEmitida.pdf")
            if os.path.exists(ruta_pdf):
                os.remove(ruta_pdf)

            ruta_formateada = os.path.join(download_dir, "CartolaEmitidaFormateada.xlsx")
            if os.path.exists(ruta_formateada):
                os.remove(ruta_formateada)

            # Configura las preferencias de descarga de Chrome
            chrome_prefs = {
                "download.default_directory": download_dir,   # Directorio donde se guardarán los archivos descargados
                "download.prompt_for_download": False,        # Evita la ventana emergente de confirmación
                "download.directory_upgrade": True,           # Permite que se cambie el directorio
                "safebrowsing.enabled": True                  # Habilita la navegación segura (puede ayudar a evitar la descarga de archivos peligrosos)
            }

            # Configura el navegador (en este caso, Chrome) NEW
            options = webdriver.ChromeOptions()
            options.add_argument("--no-sandbox")
            options.add_argument("--disable-dev-shm-usage")
            options.add_experimental_option("prefs", chrome_prefs)  # Aplica las preferencias de descarga
            # options.add_argument("--headless")  # Si deseas que el navegador se ejecute en modo sin cabeza (sin GUI)
            options.add_argument("--disable-extensions")  # Desactiva las extensiones para mejorar la velocidad y evitar interferencias
            options.add_argument("--disable-software-rasterizer")  # Desactiva el uso de software para la aceleración gráfica (mejora el rendimiento)

            #php
            options.add_argument("--window-size=1920,1080")
            options.add_argument("--disable-gpu")

            #TEST TIMER
            #options.set_capability("pageLoadStrategy", "normal")  # Puede usar 'eager' si es más rápido
            

            # Inicia el navegador con las opciones especificadas
            driver = webdriver.Chrome(options=options)
            driver.maximize_window()


            #testTEST
            try:
                driver.set_page_load_timeout(tiempo_espera)
                driver.get(url)
                print("Página cargada exitosamente.")

            except TimeoutException as te:
                print(f"Ocurrió un error: Timeout al intentar cargar {url}, se esperaron {tiempo_espera}")
                driver.quit()
                raise te
            except Exception as e:
                print(f"Ocurrió un error inesperado: {e}, se esperaron {tiempo_espera}")
                driver.quit()
                raise e
                    
            #testTEST

            # Navega a Google
            #driver.get(url)


            # -------------------------------------------- INICIO DE CUENTA -06 --------------------------------------------
            #-------------Agregar credenciales
            elemento_usuario = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.XPATH, "//*[@id='iduserName']")))
            elemento_usuario.send_keys(usuarioB)
            time.sleep(0.5)

            elemento_pass = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.NAME, "password")))
            elemento_pass.send_keys(contrasena)
            time.sleep(0.5)

            #Presionar boton "Ingresar"
            boton_ingresar = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.XPATH, "//*[@id='idIngresar']")))
            # Hacer clic en el botón
            boton_ingresar.click()
            time.sleep(7)
            #---------------Fin credenciales------------

            #-------------------- Boton Sucursal ---------------------------- IOPA S.A.  .
            # Botón para cambio de sucursal
            boton_empresa = WebDriverWait(driver, 10).until(EC.element_to_be_clickable((By.ID, "input-selector")))
            empresa_actual = boton_empresa.text.strip()
            #Cambio de sucursal si es no es IOPA S.A.
            if empresa_actual != 'IOPA S.A.  .':
                print("Empresa incorrecta seleccionada, cambiando a IOPA S.A.")
                
                boton_empresa.click()
                time.sleep(1)

                # Buscar todas las opciones del selector
                empresas = WebDriverWait(driver, 10).until(
                    EC.presence_of_all_elements_located((By.CSS_SELECTOR, "button.selector-empresas__datos-compania"))
                )
                
                # Buscar y hacer clic en "IOPA S.A."
                for empresa in empresas:
                    if "IOPA S.A." in empresa.text:
                        empresa.click()
                        time.sleep(2)
                        print("Empresa IOPA S.A. seleccionada.")
                        break
                else:
                    print("No se encontró la opción 'IOPA S.A.' en la lista.")
            time.sleep(3)

            #-------------------- Boton Sucursal ----------------------------

            #------------------Proceso descargar cartola del banco ------------------------------
            # Botón "Saldos y movimientos" de cuenta -05
            saldos_movimientos = WebDriverWait(driver, 10).until(
                EC.presence_of_element_located((By.XPATH, "//*[@id='main']/hydra-mf-pemp-home-root/div/div/hydra-main/main/article/div/section[1]/hydra-saldos-movimientos-mf/div/div[2]/hydra-movimientos/section/div[1]/div/a/bch-button/div/button")))
            saldos_movimientos.click()
            time.sleep(3)

            #Rescata dato nro de cuenta corriente y realiza validación
            elemento_cuenta_corrinte = WebDriverWait(driver, 10).until(
                EC.presence_of_element_located((By.XPATH, "//*[@id='main']/hydra-mf-pemp-prd-cta-movimientos/div/div/hydra-main/div/div/div[1]/div[1]/div[1]/section/hydra-selector-producto-saldos/button")))
            # Obtener el texto del elemento
            cuentaCorriente_extraido = elemento_cuenta_corrinte.text
            # Dividir la cadena por el salto de línea '\n'
            partes = cuentaCorriente_extraido.split('\n')
            # Obtener el texto después del salto de línea
            numero_cuenta = partes[1]
            if (numero_cuenta != cuenta_corriente ):
                print("Las cuentas no coinciden")
                elemento_cuenta_corrinte.click()
                time.sleep(2)
                # selecciona radio buton con el numero de cuenta 
                select_radio = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.XPATH, "//*[@id='0']")))
                select_radio.click()
                time.sleep(2)
                # presionar aceptar cambios
                presionar_aceptar = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.XPATH, " //*[@id='mat-dialog-0']/hydra-modal/div/div[2]/bch-button[2]/div/button")))
                presionar_aceptar.click()
                time.sleep(2)
                

            #Botón "Cartola Historica" de cuenta -05
            cartola_historica = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.XPATH, "//*[@id='main']/hydra-mf-pemp-prd-cta-movimientos/div/div/hydra-main/div/div/div[3]/div/section/bch-tabs/div/nav/div[2]/div/div/a[2]")))
            # //*[@id="main"]/hydra-mf-pemp-prd-cta-movimientos/div/div/hydra-main/div/div/div[3]/div/section/bch-tabs/div/nav/div[2]/div/div/a[2]
            cartola_historica.click()
            time.sleep(2)

            # Obtener fecha de la ultima cartola descargada
            elemento_cartola = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.XPATH, "//*[@id='download-0']/div/div[1]/div/div[2]/div[2]/p/span")))
            fecha_cartola = elemento_cartola.text

            
            #Botón "Descargar" de cuenta -05
            descargar = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.XPATH, "//*[@id='download-0']/div/div[2]/div/bch-button/div/button")))
            descargar.click()
            time.sleep(2)

            #descargar = WebDriverWait(driver, 10).until(EC.element_to_be_clickable((By.XPATH, "//button[.//span[contains(text(), 'Descargar')]]")))
            #descargar.click()
            #time.sleep(2)
#
            #Botón EXCEL de cuenta -05
            boton_excel = WebDriverWait(driver, 10).until(EC.element_to_be_clickable((By.XPATH, "//button[contains(@class, 'mat-menu-item') and contains(., 'Excel')]")))
            boton_excel.click()
            time.sleep(5)
#
            #Botón "Descargar" de cuenta -05
            descargar = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.XPATH, "//*[@id='download-0']/div/div[2]/div/bch-button/div/button")))
            descargar.click()
            time.sleep(2)
#
            #Botón PDF de cuenta -05
            descargar_tipo_archivo2 = WebDriverWait(driver, 10).until(
                EC.element_to_be_clickable((By.XPATH, "//button[normalize-space(text())='PDF']"))
            )
            descargar_tipo_archivo2.click()
            time.sleep(6)
            # -------------------------------------------- FIN DE CUENTA -05 --------------------------------------------
#
            # -------------------------------------------- INICIO DE CUENTA -06 --------------------------------------------
            # Nueva carpeta para cuenta 80-06
            download_dir_8006 = os.path.join(carpeta_dia, '80-06')
            os.makedirs(download_dir_8006, exist_ok=True)

            # Elimina archivos anteriores si existen
            ruta_excel_8006 = os.path.join(download_dir_8006, "CartolaEmitida.xls")
            if os.path.exists(ruta_excel_8006):
                os.remove(ruta_excel_8006)

            ruta_pdf_8006 = os.path.join(download_dir_8006, "CartolaEmitida.pdf")
            if os.path.exists(ruta_pdf_8006):
                os.remove(ruta_pdf_8006)

            ruta_formateada_8006 = os.path.join(download_dir, "CartolaEmitidaFormateada.xlsx")
            if os.path.exists(ruta_formateada_8006):
                os.remove(ruta_formateada_8006)

            # Cambiar directorio de descarga en tiempo real
            driver.execute_cdp_cmd("Page.setDownloadBehavior", {
                "behavior": "allow",
                "downloadPath": download_dir_8006
            })

            # Hacer clic en el botón que contiene la cuenta "00-178-02933-05"
            boton_cuenta = WebDriverWait(driver, 10).until(EC.element_to_be_clickable((By.XPATH, "//button[contains(@aria-label, '00-178-02933-05')]")))
            boton_cuenta.click()
            time.sleep(2)
#
#
            cuenta_buscada = "Cuenta Corriente 00-178-00980-06 (CLP)"
            # Esperar que el label con ese texto esté disponible y hacer clic
            radio_label = WebDriverWait(driver, 10).until(EC.element_to_be_clickable((By.XPATH,f"//label[.//span[contains(normalize-space(), '{cuenta_buscada}')]]")))
            radio_label.click()
            time.sleep(2)
#
            # Esperar a que el botón que contiene "Aceptar" sea clickeable
            boton_aceptar = WebDriverWait(driver, 10).until(EC.element_to_be_clickable((By.XPATH,"//button[.//span[contains(text(), 'Aceptar')]]")))
            boton_aceptar.click()
            time.sleep(5)

            # Botón "Cartola Histórica"
            cartola_historica = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.XPATH, "//*[@id='main']/hydra-mf-pemp-prd-cta-movimientos/div/div/hydra-main/div/div/div[3]/div/section/bch-tabs/div/nav/div[2]/div/div/a[2]")))
            cartola_historica.click()
            time.sleep(2)

            # Obtener fecha de la última cartola
            elemento_cartola = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.XPATH, "//*[@id='download-0']/div/div[1]/div/div[2]/div[2]/p/span")))
            fecha_cartola = elemento_cartola.text

            # Botón Descargar
            descargar = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.XPATH, "//*[@id='download-0']/div/div[2]/div/bch-button/div/button")))
            descargar.click()
            time.sleep(2)

            # Botón Excel
            boton_excel = WebDriverWait(driver, 10).until(EC.element_to_be_clickable((By.XPATH, "//button[contains(@class, 'mat-menu-item') and contains(., 'Excel')]")))
            boton_excel.click()
            time.sleep(5)

            # Botón Descargar
            descargar = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.XPATH, "//*[@id='download-0']/div/div[2]/div/bch-button/div/button")))
            descargar.click()
            time.sleep(2)

            # Botón PDF
            boton_pdf = WebDriverWait(driver, 10).until(EC.element_to_be_clickable((By.XPATH, "//button[normalize-space(text())='PDF']")))
            boton_pdf.click()
            time.sleep(6)

            # -------------------------------------------- FIN DE CUENTA -06 --------------------------------------------

            #registro_log(f"Cartola del banco chile descargada con exito fecha: {fecha_cartola}")
            
        
        else:
            #log_exception(f"No se encontraron credenciales para {usuario}")
            print("No se encontraron credenciales.")

    except Exception as e:
        #log_exception(f"Error banco_chile: {e}")  # Registrar la excepción en el archivo de log
        print(f"Ocurrió un error: {e}")
    finally:
        if driver is not None:
            driver.quit()

#MAIN
def ejecutar_con_reintentos(usuario, max_reintentos=3):
    # --- INICIO: LIMPIEZA DE CARPETAS AL COMIENZO DE LA EJECUCIÓN ---
    fecha_ruta_log = datetime.now().strftime("%d-%m") # Formato dd-mm
    fecha_ruta_documento = datetime.now().strftime("%d-%m-%Y") # Formato dd-mm-yyyy

    # Ruta a la carpeta de logs local (Desktop\robotpy) a eliminar
    carpeta_logs_local_a_eliminar = os.path.join(r"C:\xampp\htdocs\robot-contabilidad-chile\robotpy", "logs", "banco_chile", fecha_ruta_log)
    # Ruta a la carpeta de documentos (Desktop\robotpy) a eliminar
    carpeta_documentos_a_eliminar = os.path.join(r"C:\xampp\htdocs\robot-contabilidad-chile\robotpy", "documentos", "banco_chile", fecha_ruta_documento)
    # Ruta a la carpeta de logs en el servidor web (xampp) a eliminar
    carpeta_xampp_logs_a_eliminar = os.path.join(r"C:\xampp\htdocs\robot-contabilidad-chile\robotphp\logs", "banco_chile", fecha_ruta_log)

    print(f"Iniciando limpieza de carpetas antiguas para la fecha: {fecha_ruta_documento}")

    # Intentar eliminar carpeta de logs local
    if os.path.exists(carpeta_logs_local_a_eliminar):
        try:
            shutil.rmtree(carpeta_logs_local_a_eliminar)
            print(f"🗑️ Carpeta de logs local eliminada: {carpeta_logs_local_a_eliminar}")
        except OSError as e:
            print(f"⚠️ Error al eliminar carpeta de logs local {carpeta_logs_local_a_eliminar}: {e}")
    else:
        print(f"ℹ️ Carpeta de logs local no encontrada, no se eliminó: {carpeta_logs_local_a_eliminar}")

    # Intentar eliminar carpeta de documentos
    if os.path.exists(carpeta_documentos_a_eliminar):
        try:
            shutil.rmtree(carpeta_documentos_a_eliminar)
            print(f"🗑️ Carpeta de documentos eliminada: {carpeta_documentos_a_eliminar}")
        except OSError as e:
            print(f"⚠️ Error al eliminar carpeta de documentos {carpeta_documentos_a_eliminar}: {e}")
    else:
        print(f"ℹ️ Carpeta de documentos no encontrada, no se eliminó: {carpeta_documentos_a_eliminar}")
    
    # Intentar eliminar carpeta de logs en XAMPP
    if os.path.exists(carpeta_xampp_logs_a_eliminar):
        try:
            shutil.rmtree(carpeta_xampp_logs_a_eliminar)
            print(f"🗑️ Carpeta de logs en XAMPP eliminada: {carpeta_xampp_logs_a_eliminar}")
        except OSError as e:
            print(f"⚠️ Error al eliminar carpeta de logs en XAMPP {carpeta_xampp_logs_a_eliminar}: {e}")
    else:
        print(f"ℹ️ Carpeta de logs en XAMPP no encontrada, no se eliminó: {carpeta_xampp_logs_a_eliminar}")

    # --- FIN: LIMPIEZA DE CARPETAS ---

    intento = 1
    while intento <= max_reintentos:
        try:
            print(f"Intento {intento} de {max_reintentos}")

            # Paso 1: descarga
            banco_chile(usuario, timeout_sesion)  
            
            # Validar si existen archivos descargados (ambas cuentas opcionalmente)
            fecha_hoy = datetime.today().strftime("%d-%m-%Y")
            base_path = os.path.join(directorio_raiz, "banco_chile", fecha_hoy)
            cuenta_33 = os.path.join(base_path, "33-05", "CartolaEmitida.xls")
            cuenta_80 = os.path.join(base_path, "80-06", "CartolaEmitida.xls")
            cuenta_33_pdf = os.path.join(base_path, "33-05", "CartolaEmitida.pdf")
            cuenta_80_pdf = os.path.join(base_path, "80-06", "CartolaEmitida.pdf")
            cuenta_33_formateada = os.path.join(base_path, "33-05", "CartolaEmitidaFormateada.xlsx")
            cuenta_80_formateada = os.path.join(base_path, "80-06", "CartolaEmitidaFormateada.xlsx")

            if not os.path.exists(cuenta_33) and not os.path.exists(cuenta_80):
                raise FileNotFoundError("No se encontró ningún archivo .xls en ninguna cuenta.")
            
            if not os.path.exists(cuenta_33_pdf) and not os.path.exists(cuenta_80_pdf):
                raise FileNotFoundError("No se encontró ningún archivo .pdf en ninguna cuenta.")

            # Paso 2: formateo
            procesar_cartola()
            if not os.path.exists(cuenta_33_formateada) and not os.path.exists(cuenta_80_formateada):
                raise FileNotFoundError("No se encontró ningún archivo .xlsx formateado en ninguna cuenta.")
            
            # Paso 3
            generar_log()         

            print(f"Ejecución exitosa. Finalizó con un total de {intento} intento(s)")
            return  # Éxito, salir del bucle

        except Exception as e:
            print(f"Error en intento {intento}: {e}")
            intento += 1
            time.sleep(5)  # Espera entre reintentos

    print(f"❌ Falló después de {max_reintentos} intentos.")

#MAIN

#FORMATEO DE DATOS (SUB-FUNCTION)
def formatear_monto(valor):
    if not valor:
        return "0"
    
    # Elimina espacios y símbolos no numéricos, dejando solo dígitos
    valor = valor.strip().replace(".", "").replace(",", "").replace("$", "").replace("+", "")
    
    # Quita ceros a la izquierda
    valor = valor.lstrip("0")
    
    # Si quedó vacío después de eliminar ceros, es porque era 0
    return valor if valor else "0"

#FIN FORMATEO DE DATOS (SUB-FUNCTION)

# EXTRACCIÓN DE EXCEL Y FORMATEO (MAIN)
def procesar_cartola():
    # 1. Obtener la fecha actual
    fecha_hoy = datetime.now().strftime("%d-%m-%Y")

    # 2. Definir ruta base de los documentos
    base_dir = r"C:\xampp\htdocs\robot-contabilidad-chile\robotpy\documentos\banco_chile"

    # 3. Lista de cuentas a procesar
    cuentas = ["33-05", "80-06"]

    # 4. Palabras que causan exclusión de filas (Base inmutable)
    palabras_base_excluidas = ["fonasa","prestadores fonasa","cheque","0616084089","0776016489"]

    # 5. Procesar cada cuenta
    for cuenta in cuentas:
        registros_omitidos_abono = 0  # NUEVO: Contador para abonos vacíos o cero

        # 6. Construir ruta del archivo .xls original
        carpeta_cuenta = os.path.join(base_dir, fecha_hoy, cuenta)
        ruta_origen = os.path.join(carpeta_cuenta, "CartolaEmitida.xls")

        # 7. Validar existencia del archivo
        if not os.path.exists(ruta_origen):
            print(f"No se encontró el archivo .xls para la cuenta {cuenta} en la ruta: {ruta_origen}")
            continue

        # 8. Leer y limpiar datos del archivo
        datos_limpios = []
        try:
            with open(ruta_origen, 'r', encoding='latin-1') as archivo:
                lector = csv.reader(archivo, delimiter=';')
                for fila in lector:
                    datos_limpios.append(fila)
        except Exception as e:
            print(f"Error al leer el archivo CSV para la cuenta {cuenta} ({ruta_origen}): {e}")
            continue

        # 9. Filtrar y formatear los datos
        datos_formateados = []

        current_palabras_excluidas = list(palabras_base_excluidas)

        if cuenta == "80-06":
            current_palabras_excluidas.append("traspaso a cuenta:1780293305")

        for i, fila in enumerate(datos_limpios):
            if i == 0 or len(fila) < 2:
                continue  # Omitimos la fila con el nombre de la cuenta y filas vacías

            if i == 1:
                datos_formateados.append(fila)  # Guardamos encabezado
                continue

            detalle = str(fila[1]).lower().strip()

            if any(palabra in detalle for palabra in current_palabras_excluidas):
                continue

            # Validación: si "Deposito o Abono" (fila[3]) es cero, omitir
            try:
                abono_float = float(str(fila[3]).replace('.', '').replace(',', '.'))
                if abono_float == 0.0:
                    registros_omitidos_abono += 1
                    continue
            except ValueError:
                registros_omitidos_abono += 1
                continue

            fila[3] = formatear_monto(fila[3])  # Depósito o Abono
            fila[4] = formatear_monto(fila[4])  # Saldo

            datos_formateados.append(fila)


        # 10. Crear archivo Excel formateado
        wb = openpyxl.Workbook()
        ws = wb.active
        ws.title = "Cartola"

        # 11. Escribir los datos en la hoja de cálculo
        for fila in datos_formateados:
            ws.append(fila)

        # 12. Guardar el nuevo archivo Excel
        ruta_salida = os.path.join(carpeta_cuenta, "CartolaEmitidaFormateada.xlsx")
        wb.save(ruta_salida)
        print(f"Cartola de la cuenta {cuenta} formateada y guardada como: {ruta_salida}")

        # 13. Verificar si se generaron suficientes datos
        if len(datos_formateados) <= 1:
            print(f"⚠️ Advertencia: La cartola para la cuenta {cuenta} contiene pocos datos después del formateo.")
        else:
            print(f"✅ La cartola de la cuenta {cuenta} fue procesada exitosamente con {len(datos_formateados) - 1} registros de movimientos.")

        print(f"ℹ️ Se omitieron {registros_omitidos_abono} registros con abonos vacíos o iguales a 0 en la cuenta {cuenta}.")  # NUEVO

    # 14. Fin de la función

# FIN EXTRACCIÓN DE EXCEL Y FORMATEO (MAIN)

# CREACION DE LOGS
def generar_log():
    fecha_actual = datetime.now().strftime("%d-%m-%Y")
    fecha_ruta = datetime.now().strftime("%d-%m")
    base_dir = r"C:\xampp\htdocs\robot-contabilidad-chile\robotpy"
    cuentas = ["33-05", "80-06"]
    nombre_clinica = "Clínica Iopa"
    nombre_robot = "Robot Contabilidad Web-Scrapping"

    carpeta_logs = os.path.join(base_dir, "logs", "banco_chile", fecha_ruta)
    os.makedirs(carpeta_logs, exist_ok=True)

    for cuenta in cuentas:
        ruta_excel = os.path.join(base_dir, "documentos", "banco_chile", fecha_actual, cuenta, "CartolaEmitidaFormateada.xlsx")
        ruta_log = os.path.join(carpeta_logs, f"log_{cuenta}.log")
        destino_web = os.path.join(r"C:\xampp\htdocs\robot-contabilidad-chile\robotphp\logs\banco_chile", fecha_ruta)
        os.makedirs(destino_web, exist_ok=True)
        ruta_json = os.path.join(destino_web, f"datos_{cuenta}.json")

        # Eliminar archivos previos si existen
        if os.path.exists(ruta_log):
            try:
                os.remove(ruta_log)
                print(f"Archivo .log anterior eliminado: {ruta_log}")
            except Exception as e:
                print(f"⚠️ Error al eliminar archivo .log previo: {e}")

        if os.path.exists(ruta_json):
            try:
                os.remove(ruta_json)
                print(f"Archivo .json anterior eliminado: {ruta_json}")
            except Exception as e:
                print(f"⚠️ Error al eliminar archivo .json previo: {e}")

        with open(ruta_log, 'w', encoding='utf-8') as log:
            def escribir_linea(texto):
                print(texto)
                log.write(texto + '\n')
                log.flush() # Fuerza la escritura al disco de cada línea

            escribir_linea("************** Ejecución de robot **************")
            escribir_linea(f"Clínica: {nombre_clinica}")
            escribir_linea(f"Fecha y hora: {datetime.now().strftime('%d-%m-%Y %H:%M:%S')}")
            escribir_linea(f"Robot: {nombre_robot}")
            escribir_linea(f"Cuenta procesada (Nro. Cuenta Bancaria - últimos 4 dígitos): {cuenta}\n")

            # Inicializar df_log para asegurar que esté definida antes del cálculo de sumatoria
            df_log = pd.DataFrame() 
            columnas_mapeo = None # Resetear en cada iteración

            if not os.path.exists(ruta_excel):
                escribir_linea(f"⚠️ No se encontró el archivo Excel para la cuenta {cuenta}:")
                escribir_linea(ruta_excel)
                # Guardar JSON con mensaje de error específico
                json_data = {
                    "mensaje": "No se encontró el archivo Excel para esta cuenta"
                }
                with open(ruta_json, 'w', encoding='utf-8') as f_json:
                    json.dump(json_data, f_json, ensure_ascii=False, indent=4)
            else:
                try:
                    df = pd.read_excel(ruta_excel, header=0)
                    if df.empty:
                        escribir_linea("No se encontraron transacciones para esta fecha.\n")
                        # Guardar JSON con mensaje de que no hay transacciones
                        json_data = {
                            "mensaje": "No existen transacciones asociadas al día seleccionado"
                        }
                        with open(ruta_json, 'w', encoding='utf-8') as f_json:
                            json.dump(json_data, f_json, ensure_ascii=False, indent=4)
                    else: # El DataFrame no está vacío
                        # Limpiar nombres columnas
                        df.columns = [col.strip().lower().replace("  ", " ").replace(" ", "_") for col in df.columns]

                        # Mapear columnas de interés (SE HA ELIMINADO 'cheque_o_cargo')
                        columnas_mapeo = { 
                            'fecha': None,
                            'detalle_movimiento': None,
                            'deposito_o_abono': None,
                            #'cheque_o_cargo': None, # <--- COLUMNA ELIMINADA POR SOLICITUD
                            'docto._nro.': None,
                            'sucursal': None
                        }
                        for col in df.columns:
                            for clave in columnas_mapeo:
                                if clave in col:
                                    columnas_mapeo[clave] = col

                        columnas_encontradas = [col for col in columnas_mapeo.values() if col is not None]

                        if not columnas_encontradas:
                            escribir_linea("⚠️ No se encontraron columnas válidas en el archivo.\n")
                            # No se crea JSON si no hay columnas válidas según la lógica original.
                        else: # Se encontraron columnas válidas
                            df_log = df[columnas_encontradas].copy()

                            # YA NO HAY FILTRADO POR 'cheque_o_cargo' AQUÍ.
                            # df_log ahora contiene todas las filas con las columnas seleccionadas.

                            # Asegurar tipos de cadena para columnas específicas (si existen)
                            for col in ['detalle_movimiento', 'sucursal']:
                                if col in df_log.columns:
                                    df_log[col] = df_log[col].astype(str).str.strip()

                            # Comprobar si df_log está vacío después de la selección de columnas
                            if df_log.empty:
                                escribir_linea("No se encontraron transacciones en las columnas seleccionadas.\n")
                                # Guardar JSON con mensaje de que no hay transacciones en columnas válidas
                                json_data = {
                                    "mensaje": "No se encontraron transacciones en las columnas seleccionadas para esta cuenta."
                                }
                                with open(ruta_json, 'w', encoding='utf-8') as f_json:
                                    json.dump(json_data, f_json, ensure_ascii=False, indent=4)
                            else:
                                escribir_linea("Datos encontrados:\n")
                                df_str = df_log.to_string(index=False)
                                print(df_str) # Imprime en consola
                                log.write(df_str + "\n\n") # Escribe en el log

                                # Guardar JSON con las transacciones filtradas (ahora solo por columnas)
                                df_log.to_json(ruta_json, orient='records', force_ascii=False, indent=4)
                                print(f"JSON guardado en -> {ruta_json}")

                except Exception as e:
                    escribir_linea(f"❌ Error al leer o procesar el archivo Excel: {e}")
                    # Guardar JSON con mensaje de error
                    json_data = {
                        "clinica": nombre_clinica,
                        "fecha_hora": datetime.now().strftime('%d-%m-%Y %H:%M:%S'),
                        "robot": nombre_robot,
                        "cuenta": cuenta,
                        "transacciones": [],
                        "mensaje": f"Error al leer el archivo Excel: {str(e)}"
                    }
                    with open(ruta_json, 'w', encoding='utf-8') as f_json:
                        json.dump(json_data, f_json, ensure_ascii=False, indent=4)

            # Calcular la sumatoria de la columna 'deposito_o_abono' (al final, después del procesamiento)
            # Solo intenta si df_log ha sido creado y tiene la columna 'deposito_o_abono'
            if columnas_mapeo and 'deposito_o_abono' in columnas_mapeo and columnas_mapeo['deposito_o_abono'] and not df_log.empty and columnas_mapeo['deposito_o_abono'] in df_log.columns:
                columna_deposito = columnas_mapeo['deposito_o_abono']
                df_log[columna_deposito] = pd.to_numeric(df_log[columna_deposito], errors='coerce').fillna(0)
                total_depositos = int(df_log[columna_deposito].sum()) 
                escribir_linea(f"Total de ingresos: {format(total_depositos, ',').replace(',', '.')}\n")
            else:
                # Modificado para reflejar que la columna no está disponible o no hay datos para sumar.
                escribir_linea("⚠️ No se pudo calcular la sumatoria total de ingresos: columna 'deposito_o_abono' no encontrada o no hay datos válidos.\n")
            
            escribir_linea("************** Fin Ejecución de robot **************\n")
            log.flush() # Fuerza la escritura de todo el contenido del log antes de copiar

            # Copiar log al servidor
            destino_log = os.path.join(destino_web, f"log_{cuenta}.log")
            try:
                shutil.copy(ruta_log, destino_log)
                print(f"Copia del log guardada en -> {destino_log}")
            except Exception as e:
                print(f"Error al copiar el log a la ruta web: {e}")

# FIN CREACION DE LOGS




#CREDENCIALES (PARA FUNCIONES)
param_banco = 'BANCO DE CHILE'
param_prevision = 'FONASA'
usuario = 'usuarioBdChile'


ejecutar_con_reintentos(usuario) #FUNCION PRINCIPAL
#procesar_cartola()#debug
#generar_log()#debug
#banco_chile(usuario)

