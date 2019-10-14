var currentTablePage = readCookie('tablePage') || 1;

function ingresoError(msg) {
    let $select_horarios = document.querySelectorAll('.horarios');
    $select_horarios.forEach(function ($select) {
        $select.style.border = '2px solid red';
    });
    let $divErrorReserva = document.querySelector('#errorReserva');
    $textErrorReserva = document.createTextNode(msg);
    $divErrorReserva.appendChild($textErrorReserva);
}

function mostrarTabla(datosTabla, division, mostrar) {
    let $tabla = document.querySelector('#reservas-tabla');
    let grupo_contador = 1;
    for(let i = 0; i < datosTabla.length; i++) {
        let $new_row = document.createElement('tr');
        if(i % 2 == 0) $new_row.classList.add('even');
        if(i < division*grupo_contador) {
            $new_row.classList.add('grupo' + grupo_contador);
        }
        if(mostrar != grupo_contador) {
            $new_row.classList.add('remove');
        }
        let filaTabla = datosTabla[i];
        let $new_column;
        Object.values(filaTabla).forEach(function (x) {
            $new_column = document.createElement('td');
            $new_column.appendChild(document.createTextNode(x));
            $new_row.appendChild($new_column);
        });
        $tabla.appendChild($new_row);  
        if (i == (division*grupo_contador - 1)) {
            grupo_contador++;
        }
    }
}

function actualizarTabla(mostrar) {
    $filas = document.querySelectorAll('tr');
    $filas.forEach(function (fila) {
        clases = fila.className.split(' ');
        if(clases.includes('table-header') || clases.includes('grupo' + mostrar)) {
            fila.classList.remove('remove');
        } else {
            fila.classList.add('remove');
        }
    });
}

function mostrarBotones(datosTabla, $contenedor, division) {
    let cantidadBotones = Math.ceil(datosTabla.length/division)

    for(let i = 0; i < cantidadBotones; i++) {
        $button = document.createElement('button');
        $button.type = 'button';
        $button.classList.add('nav-button')
        $button.value = i+1;

        $button.appendChild(document.createTextNode(i + 1));
        $contenedor.appendChild($button);
    }
}

function chequeoBotones() {
    $botones = document.querySelectorAll('.nav-button');
    $botones.forEach(function (boton) {
        boton.onclick = function() {
            actualizarTabla(boton.value);
            setCookie('tablePage', boton.value);
        }
    });
}

function setCookie(name, value) {
    let cookieString = name + '=' + value + ';expires=' + ';path=/';
    document.cookie = cookieString;
}

function readCookie(name) {
    let cookieArray = document.cookie.split('; ');
    let foundCookie;
    cookieArray.forEach(function(cookie) {
        let subCookie = cookie.split('=');
        if(subCookie[0] == name) {
            foundCookie = subCookie[1];
        }
    });
    return foundCookie;
}
