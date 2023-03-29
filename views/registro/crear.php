<main class="registro">
    <h2 class="registro__heading"><?php echo $titulo; ?></h2>
    <p class="registro__descripcion">Elige tu plan</p>

    <div class="paquetes__grid">
        <div class="paquete">
            <h3 class="paquete__nombre">Pase Gratis</h3>
            <ul class="paquete__lista">
                <li class="paquete__elemento">Acceso Virtual a DevWebCamp</li>
            </ul>

            <p class="paquete__precio">$0</p>

            <form method="POST" action="/finalizar-registro/gratis">
                <input class="paquetes__submit" type="submit" value="Inscripción Gratis">
            </form>
        </div>

        <div class="paquete">
            <h3 class="paquete__nombre">Pase Presencial</h3>
            <ul class="paquete__lista">
                <li class="paquete__elemento">Acceso Presencial a DevWebCamp</li>
                <li class="paquete__elemento">Pase por 2 días</li>
                <li class="paquete__elemento">Acceso a talleres y conferencias</li>
                <li class="paquete__elemento">Acceso a las grabaciones</li>
                <li class="paquete__elemento">Camisa del Evento</li>
                <li class="paquete__elemento">Comida y Bebida</li>
            </ul>

            <p class="paquete__precio">$199</p>

            <div id="smart-button-container">
                <div style="text-align: center;">
                    <div id="paypal-button-container"></div>
                </div>
            </div>

            
        </div>

        <div class="paquete">
            <h3 class="paquete__nombre">Pase Virtual</h3>
            <ul class="paquete__lista">
                <li class="paquete__elemento">Acceso Virtual a DevWebCamp</li>
                <li class="paquete__elemento">Pase por 2 días</li>
                <li class="paquete__elemento">Acceso a talleres y conferencias</li>
                <li class="paquete__elemento">Acceso a las grabaciones</li>
            </ul>

            <p class="paquete__precio">$49</p>

            <div id="smart-button-container">
                <div style="text-align: center;">
                  <div id="paypal-button-container-virtual"></div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- aqui empieza lo de paypal -->

<!-- <div id="smart-button-container">
  <div style="text-align: center;">
    <div id="paypal-button-container"></div>
  </div>
</div>

  <script src="https://www.paypal.com/sdk/js?client-id=AezGSwOWtiaK0a2kmHEB3Ohmc434Yx5UOwWo9-nWb9U5BnjCWbRjTNSJaDPmBdQ9lAtnUfRuksi2-8RG&enable-funding=venmo&currency=USD" data-sdk-integration-source="button-factory"></script>
  <script>
    function initPayPalButton() {
      paypal.Buttons({
        style: {
          shape: 'rect',
          color: 'blue',
          layout: 'vertical',
          label: 'buynow',
          
        },

        createOrder: function(data, actions) {
          return actions.order.create({
            purchase_units: [{"description":"Pago Presencial DevWebCamp","amount":{"currency_code":"USD","value":199}}]
          });
        },

        onApprove: function(data, actions) {
          return actions.order.capture().then(function(orderData) {
            
            // Full available details
            console.log('Capture result', orderData, JSON.stringify(orderData, null, 2));

            // Show a success message within this page, e.g.
            const element = document.getElementById('paypal-button-container');
            element.innerHTML = '';
            element.innerHTML = '<h3>Thank you for your payment!</h3>';

            // Or go to another URL:  actions.redirect('thank_you.html');
            
          });
        },

        onError: function(err) {
          console.log(err);
        }
      }).render('#paypal-button-container');
    }
    initPayPalButton();
  </script> -->

  <!-- ESTE ES DEL PROFESOR -->
  <!-- <script src="https://www.paypal.com/sdk/js?client-id=Adc6YGqAvfmtD_7WCDB9mf3AidMfM18ZQr49mGkIHEOF8XuFTW7aAMFuB09wVfMsKy54lOoFfpWqL3HS&enable-funding=venmo&currency=USD" data-sdk-integration-source="button-factory"></script> -->

  <!-- mi script de paypal -->

  
<script src="https://www.paypal.com/sdk/js?client-id=AezGSwOWtiaK0a2kmHEB3Ohmc434Yx5UOwWo9-nWb9U5BnjCWbRjTNSJaDPmBdQ9lAtnUfRuksi2-8RG&enable-funding=venmo&currency=USD" data-sdk-integration-source="button-factory"></script>
  <script>
    function initPayPalButton() {
      paypal.Buttons({//estilos de los botones
        style: {
          shape: 'rect',
          color: 'blue',
          layout: 'vertical',
          label: 'pay',
        },

        createOrder: function(data, actions) {
          return actions.order.create({
            purchase_units: [{"description":"1","amount":{"currency_code":"USD","value":199}}]//aqui el numero 1 es del pase presencial
          });
        },

        onApprove: function(data, actions) {//envia la peticion hacia nuestra api
          return actions.order.capture().then(function(orderData) {
            
                const datos = new FormData();//purchase_units[0] es un array y se va a la posicion 0 (o sea la primera)
                datos.append('paquete_id', orderData.purchase_units[0].description);//esta es la descripcion de lo que se esta pagando "paquete presencial"
                datos.append('pago_id', orderData.purchase_units[0].payments.captures[0].id);//esto es lo que genera paypal el id cuando se compra algo
                // console.log(datos)
                fetch('/finalizar-registro/pagar', {
                    method: 'POST',
                    body: datos
                })
                .then( respuesta => respuesta.json())//aqui lo envia y php guarda los datos
                .then(resultado => {//aqui php te da un resultado (si se pudo o no)
                  console.log(resultado);
                    if(resultado.resultado) {//si hay un resultado correcto redirecciona
                        actions.redirect('http://localhost:3000/finalizar-registro/conferencias');
                    }
                })
            
          });
        },

        onError: function(err) {
          console.log(err);
        }
      }).render('#paypal-button-container');


      // Pase virtual
      paypal.Buttons({
        style: {
          shape: 'rect',
          color: 'blue',
          layout: 'vertical',
          label: 'pay',
        },

        createOrder: function(data, actions) {
          return actions.order.create({
            purchase_units: [{"description":"2","amount":{"currency_code":"USD","value":49}}]
          });
        },

        onApprove: function(data, actions) {
          return actions.order.capture().then(function(orderData) {

                const datos = new FormData();
                datos.append('paquete_id', orderData.purchase_units[0].description);
                datos.append('pago_id', orderData.purchase_units[0].payments.captures[0].id);

                fetch('/finalizar-registro/pagar', {
                    method: 'POST',
                    body: datos
                })
                .then( respuesta => respuesta.json())
                .then(resultado => {
                    if(resultado.resultado) {
                        actions.redirect('http://localhost:3000/finalizar-registro/conferencias');
                    }
                })
                
          });
        },

        onError: function(err) {
          console.log(err);
        }
      }).render('#paypal-button-container-virtual');

    }
    initPayPalButton();
  </script>