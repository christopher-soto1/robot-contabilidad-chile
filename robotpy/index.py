from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
import time

# Crear instancia del navegador (aquí usamos Chrome)
driver = webdriver.Chrome()

try:
    # Abrir Google
    driver.get("https://www.google.com")

    # Esperar 2 segundos para que cargue (puedes usar esperas explícitas para algo más robusto)
    time.sleep(2)

    # Encontrar el cuadro de búsqueda por nombre
    caja_busqueda = driver.find_element(By.NAME, "q")

    # Escribir texto para buscar
    caja_busqueda.send_keys("Chatgpt")

    # Presionar Enter para buscar
    caja_busqueda.send_keys(Keys.RETURN)

    # Esperar 5 segundos para ver resultados
    time.sleep(5)

finally:
    # Cerrar el navegador
    driver.quit()
