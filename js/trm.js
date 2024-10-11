function cargar_provincias()
        {
            var array = ["Cantabria", "Asturias", "Galicia", "Andalucia", "Extremadura"];
            for(var i in array)
            { 
                document.getElementById("provincia").innerHTML += "<option value='"+array[i]+"'>"+array[i]+"</option>"; 

            }
    }

    cargar_provincias();