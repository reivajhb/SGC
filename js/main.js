


let $tipo = document.getElementById('tipo')
let $retencion = document.getElementById('retencion')




let tipos = ['Sin Retenciones', 'Retefuente', 'Reteica', 'Retefuente y Reteica', 'Retenciones en Base']
let retenciones = ['0.035', '0.01', '0.04', '0.06', '0.025', '8', '6', '13.80', '9.66', '4.14', '7', 'Sin Retenciones']





function mostrarTipos(arreglo, tiporetencion) {
    let elementos = '<option selected disables>--Seleccione Retención--</option>'

    for(let i = 0; i < arreglo.length; i++) {
        elementos += '<option   value="' + arreglo[i] +'">' + arreglo[i] +'</option>'
    }

    tiporetencion.innerHTML = elementos
  
}

mostrarTipos(tipos, $tipo)

function recortar(array, inicio, fin, tiporetencion) {
    let recortar = array.slice(inicio, fin)
    mostrarTipos(recortar, tiporetencion)
}


function updateDescription() {
    // Get the selected values from the dropdowns and input fields
    var selectedTipo = document.getElementById("tipo").value;
    var selectedRetencion = document.getElementById("retencion").value;
    var dobleretencion = document.getElementById("dobleretencion").value;
    var valorestaretefuente = document.getElementById("valorestaretefuente").value;
    var valorestareteica = document.getElementById("valorestareteica").value;
    var concepto = document.getElementById("concepto").value;
    var localizador = document.getElementById("localizador").value;
    

    // Update the textarea content with the selected values
    var descripcionTextarea = document.getElementById("exampleFormControlTextarea1");
    descripcionTextarea.value =
      "Las retenciones aplicadas para este Anticipo son las siguientes:\n\n" +
      "Tipo de retención: " +
      selectedTipo +
      "\nPorcentaje de retencion Retefuente: " +
      selectedRetencion +
      "\nPorcentaje de retencion Reteica: " +
      dobleretencion +
      "\nValor a restar Retefuente: " +
      valorestaretefuente +
      "\nValor a restar Reteica: " +
      valorestareteica +
      "\nConcepto: " +
      concepto +
      "\nLocalizador: " +
      localizador;

    
  }


$tipo.addEventListener('change', function() {
    let valorselect = $tipo.value

    switch(valorselect) {
        case 'Retefuente':
            recortar(retenciones, 0, 5, $retencion)
        break
        case 'Reteica':
            recortar(retenciones, 5, 11, $retencion)
        break
        case 'Retefuente y Reteica':    
          recortar(retenciones, 0, 5, $retencion)

          alert('Se aplicara doble retención');     
        break
        case 'Sin Retenciones':    
          recortar(retenciones, 11, 12, $retencion)

          alert('No se aplicaran retenciónes');     
        break

    }

    
})

 

    const retefuente = document.querySelector('#retencion');
  console.log(retefuente)
    retefuente.addEventListener('change', () => {
      let valorOption = retefuente.value;
      console.log(valorOption);

      var optionSelect = retefuente.options[retefuente.selectedIndex];

      /*Mostrando el resultado en el input*/
     
if ($tipo.value === 'Retefuente') {
  

        contenido = document.getElementById("valor").value;

        inputResult = document.querySelector('#resultRetefuente').value=( optionSelect.value);

         x = contenido*inputResult

         valorestaretefuente = document.getElementById("valorestaretefuente").value = x;

         y = contenido - valorestaretefuente

         valorPagaretefuente = document.getElementById("valorPagaretefuente").value = y;
         ValorTotalApagar = document.getElementById("ValorTotalApagar").value = y;

    console.log("Opción:", optionSelect.text);
    console.log("Porcentaje:", optionSelect.value);
    console.log("tipo:", $tipo.value);
    console.log("Valorestar:", valorestaretefuente);
    console.log("Valor:", contenido);
    console.log("ValorPagar:", y);

      // Call the updateDescription function after setting the value
    updateDescription();
        
      } else if($tipo.value === 'Reteica'){

       

        contenido = document.getElementById("valor").value;

      inputResult = document.querySelector('#resultReteica').value=( optionSelect.value);

      x = contenido*inputResult/1000

      valoreteica = document.getElementById("valorestareteica").value = x;

      w = contenido - valoreteica

      valorPagareteica = document.getElementById("valorPagareteica").value = w;

      ValorTotalApagar = document.getElementById("ValorTotalApagar").value = w;

     console.log("Opción:", optionSelect.text);
     console.log("Porcentaje:", optionSelect.value);
     console.log("tipo:", $tipo.value); 
     console.log("Valorestar:", valoreteica);
     console.log("Valor:", contenido);
     console.log("valorpagar:", w);

     // Call the updateDescription function after setting the value
    updateDescription();


      }else if($tipo.value === 'Retefuente y Reteica'){

        contenido = document.getElementById("valor").value;

        inputResult = document.querySelector('#resultRetefuente').value=( optionSelect.value);

         x = contenido*inputResult

         valorestaretefuente = document.getElementById("valorestaretefuente").value = x;

         y = contenido - valorestaretefuente

         valorPagaretefuente = document.getElementById("valorPagaretefuente").value = y;
         


   const RetefuenteyReteica = document.querySelector('#dobleretencion');
   console.log(RetefuenteyReteica)
            RetefuenteyReteica.addEventListener('change', () => {
            let valorOption2 = RetefuenteyReteica.value;
            console.log(valorOption2);

            var optionSelectdobleretencion = RetefuenteyReteica.options[RetefuenteyReteica.selectedIndex];

      
      contenido2 = document.getElementById("valor").value;

      inputResult2 = document.querySelector('#resultReteica').value=( optionSelectdobleretencion.value);

      p = contenido2*inputResult2/1000

      valorestareteica2 = document.getElementById("valorestareteica").value = p;

      h = contenido2 - valorestareteica2

      valorPagareteica2 = document.getElementById("valorPagareteica").value = h;
      
      s = valorestareteica2 + valorestaretefuente

      sumretenciones = document.getElementById("sumretenciones").value = s;

      q = contenido2 - sumretenciones

      ValorTotalApagar = document.getElementById("ValorTotalApagar").value = q;

     console.log("Opción:", optionSelect.text);
     console.log("Porcentaje:", optionSelect.value);
     console.log("tipo:", $tipo.value); 
     console.log("valorestareteica2:", valorestareteica2);
     console.log("valorestaretefuente:", valorestaretefuente);
     console.log("Suma Retenciones:", s);
     console.log("Suma ValorTotalApagar:", q);
     console.log("Valor:", contenido2);

     // Call the updateDescription function after setting the value
    updateDescription();
     
        });
      }else if($tipo.value === 'Sin Retenciones'){

        valorSinRT = document.getElementById("valor").value ;

         ValorTotalApagar = document.getElementById("ValorTotalApagar").value = valorSinRT ;
      }





   
    
    
 

      /* Mostrando resultado en la capa capaResultado*/
      
    });