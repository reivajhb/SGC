import sys
import fitz
import json

def leer_pdf(ruta_pdf):
    texto = ""

    with fitz.open(ruta_pdf) as pdf:
        for pagina in pdf:
            texto += pagina.get_text() + "\n"

    return texto.strip()

if __name__ == "__main__":
    ruta_pdf = sys.argv[1]

    try:
        texto = leer_pdf(ruta_pdf)

        print(json.dumps({
            "ok": True,
            "texto": texto
        }, ensure_ascii=False))

    except Exception as e:
        print(json.dumps({
            "ok": False,
            "error": str(e)
        }, ensure_ascii=False))