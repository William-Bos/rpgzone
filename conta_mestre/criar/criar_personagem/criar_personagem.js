function addPericia() {
    const container = document.getElementById('pericias');
    const div = document.createElement('div');
    div.className = 'pericia';
    div.innerHTML = `
                <input type="text" name="pericia_nome[]" placeholder="Nome da Perícia" required>
                <input type="number" name="pericia_valor[]" placeholder="Valor" required>
                <button type="button" onclick="this.parentNode.remove()">Remover</button>
            `;
    container.appendChild(div);
}

function addAtributo() {
    const container = document.getElementById('atributos');
    const div = document.createElement('div');
    div.className = 'atributo';
    div.innerHTML = `
                <input type="text" name="nome_atributo[]" placeholder="Nome do Atributo" required>
                <input type="number" name="valor_atributo[]" placeholder="Valor" required>
                <input type="number" name="modificador_atributo[]" placeholder="Modificador" required>
                <button type="button" onclick="this.parentNode.remove()">Remover</button>
            `;
    container.appendChild(div);
}

function addMoeda() {
    const container = document.getElementById('carteira');

    // Pega o primeiro grupo (.moeda) para clonar
    const original = container.querySelector('.moeda');
    if (!original) return;

    // Clona o grupo
    const novo = original.cloneNode(true);

    // Reseta o select e input
    const select = novo.querySelector('select');
    select.value = '';

    const inputQtd = novo.querySelector('input[type="number"]');
    inputQtd.value = 0;

    // Adiciona o clone ao container
    container.appendChild(novo);
}


