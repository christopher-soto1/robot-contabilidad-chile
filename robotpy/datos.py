import pandas as pd

# Datos de ejemplo
datos = {
    "Producto": ["Camisa", "Pantalón", "Zapatos", "Sombrero", "Chaqueta"],
    "Cantidad": [10, 5, 8, 12, 7],
    "Precio_unitario": [20.0, 40.0, 60.0, 15.0, 80.0],
    "Descuento": [0.1, 0.15, 0.0, 0.05, 0.2]
}

# Crear el DataFrame
#df = pd.DataFrame(datos)

# Guardarlo como archivo Excel
#df.to_excel("ventas.xlsx", index=False)

#print("Archivo 'ventas.xlsx' creado.")
# Leer archivo Excel
df = pd.read_excel("ventas.xlsx")

# Mostrar los datos
print(df)

