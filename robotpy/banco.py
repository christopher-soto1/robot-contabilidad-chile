from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from webdriver_manager.chrome import ChromeDriverManager
import time
from dotenv import load_dotenv
import os

load_dotenv()  # Esto lee el archivo .env y carga las variables

usuario = os.getenv("USUARIO")
password = os.getenv("PASSWORD")

print("Usuario:", usuario)
print("Password:", password)

# Crear el servicio usando webdriver-manager (no hace falta ruta manual)
#servicio = Service(r'C:\Users\programadorll\Desktop\chromedriver-win64\chromedriver.exe')
servicio = Service(ChromeDriverManager().install())

# Opciones del navegador
opciones = webdriver.ChromeOptions()
opciones.add_argument('--start-maximized')

# Crear driver
driver = webdriver.Chrome(service=servicio, options=opciones)

# Abrir sitio web del banco
driver.get("https://sitiospublicos.bancochile.cl/personas")

time.sleep(3)
boton = driver.find_element(By.ID, "ppp_header-link-banco_en_linea")

time.sleep(2)
boton.click()

time.sleep(6)

# Cerrar navegador
driver.quit()
