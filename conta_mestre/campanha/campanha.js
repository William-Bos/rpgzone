function abrirModal(item) {
    document.getElementById('id_item').value = item.id;
    document.getElementById('nome').value = item.nome;
    document.getElementById('descricao').value = item.descricao;
    document.getElementById('ca').value = item.ca;
    document.getElementById('efeito').value = item.efeito;
    document.getElementById('dano').value = item.dano;
    document.getElementById('buff').value = item.buff;
    document.getElementById('debuff').value = item.debuff;
    document.getElementById('modal').style.display = 'flex';  // mantendo flex
}

function fecharModal() {
    document.getElementById('modal').style.display = 'none';
    // Opcional: limpar campos aqui se quiser
}

function abrirModalHabilidade(habilidade) {
    document.getElementById('id_habilidade').value = habilidade.id;
    document.getElementById('nome_habilidade').value = habilidade.nome;
    document.getElementById('descricao_habilidade').value = habilidade.descricao;
    document.getElementById('efeito_habilidade').value = habilidade.efeito || '';
    document.getElementById('dano_habilidade').value = habilidade.dano || '';
    document.getElementById('buff_habilidade').value = habilidade.buff || '';
    document.getElementById('debuff_habilidade').value = habilidade.debuff || '';
    document.getElementById('custo_habilidade').value = habilidade.custo || '';
    document.getElementById('modalHabilidade').style.display = 'flex';  // mudado para flex para ficar igual
}

function abrirModalMoeda(carteira) {
    document.getElementById('id_carteira').value = carteira.id;
    document.getElementById('nome_moeda').value = carteira.nome_moeda;
    document.getElementById('valor_base').value = carteira.valor_base;
    document.getElementById('modalMoeda').style.display = 'flex';  // mantendo flex
    
}

function fecharModalHabilidade() {
    document.getElementById('modalHabilidade').style.display = 'none';
    // Opcional: limpar campos aqui se quiser
}
function fecharModalMoeda() {
    document.getElementById('modalMoeda').style.display = 'none';
}
// Fecha qualquer modal clicando fora da área do conteúdo
window.onclick = function(event) {
    const modal = document.getElementById('modal');
    const modalHabilidade = document.getElementById('modalHabilidade');
    const modalMoeda = document.getElementById('modalMoeda');
    if (event.target === modal) {
        fecharModal();
    } else if (event.target === modalHabilidade) {
        fecharModalHabilidade();
    } else if (event.target === modalMoeda) {
        fecharModalMoeda();
    }
};

