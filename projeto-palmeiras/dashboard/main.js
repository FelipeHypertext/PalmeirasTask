const AVATAR_CLASSES = ['av-a','av-b','av-c','av-d','av-e','av-f','av-g'];
const TAG_LABELS  = { tecnico:'Técnico', auxtec:'Auxiliar técnico', prepfi:'Preparador físico', prepgoleiro:'Treinador de goleiros', analista:'Analista de desempenho', coordenador:'Coordenador técnico' };
const PRIORITY    = { high:'pri-high', med:'pri-med', low:'pri-low' };
 
let dragId = null;

let cards = typeof TAREFAS_REAIS !== 'undefined' ? TAREFAS_REAIS : [];
 
function buildCard(card) {
  const avatarsHtml = card.authors.map(a =>
    `<div class="avatar ${a.c}" title="${a.i}">${a.i}</div>`
  ).join('');
 
  return `
  <div class="card" id="card-${card.id}" draggable="true"
       ondragstart="onDragStart(event,${card.id})"
       ondragend="onDragEnd()"
       style="cursor: grab;">
    <div class="card-top">
      <span class="card-tag tag-${card.tag}">${TAG_LABELS[card.tag] || 'Membro'}</span>
      <a href="../tasks/view.php?id=${card.id}" title="Ver Detalhes" style="color: var(--text-secondary); text-decoration: none; padding: 4px;">
        <i class="ti ti-external-link" style="font-size: 16px;"></i>
      </a>
    </div>
    <div class="card-title">${escHtml(card.title)}</div>
    <div class="card-desc">${escHtml(card.desc)}</div>
    <div class="card-footer">
      <div class="card-meta">
        <div class="card-date"><i class="ti ti-calendar"></i> ${card.date}</div>
        <div class="card-comments"><i class="ti ti-message"></i> ${card.comments}</div>
      </div>
      <div class="avatars">${avatarsHtml}</div>
    </div>
  </div>`;
}
 
function escHtml(str) {
  if (!str) return '';
  return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
 
function renderAll() {
  const cols = ['pending','progress','done'];
  let total = 0;
  cols.forEach(col => {
    const colCards = cards.filter(c => c.col === col);
    const container = document.getElementById('cards-' + col);
    if(container) {
        document.getElementById('cnt-' + col).textContent = colCards.length;
        total += colCards.length;
     
        if (colCards.length === 0) {
          container.innerHTML = `<div class="col-empty"><i class="ti ti-clipboard-list"></i>Sem tarefas</div>`;
        } else {
          container.innerHTML = colCards.map(buildCard).join('');
        }
    }
  });
 
  const done = cards.filter(c => c.col === 'done').length;
  const sprintSub = document.getElementById('sprint-sub');
  if(sprintSub) sprintSub.textContent = `${total} tarefa(s) · ${done} concluída(s)`;
}
 
function onDragStart(e, id) {
  dragId = id;
  setTimeout(() => { 
      const el = document.getElementById('card-' + id);
      if(el) el.classList.add('dragging'); 
  }, 0);
}
function onDragEnd() {
  document.querySelectorAll('.card').forEach(el => el.classList.remove('dragging'));
  document.querySelectorAll('.drop-zone').forEach(z => z.classList.remove('active'));
}
function onDragOver(e, col) {
  e.preventDefault();
  const zone = document.getElementById('zone-' + col);
  if(zone) zone.classList.add('active');
}
function onDragLeave(e) {
  document.querySelectorAll('.drop-zone').forEach(z => z.classList.remove('active'));
}

function onDrop(e, col) {
  e.preventDefault();
  document.querySelectorAll('.drop-zone').forEach(z => z.classList.remove('active'));
  
  if (dragId === null) return;
  
  const card = cards.find(c => String(c.id) === String(dragId));
  
  if (card && card.col !== col) {
    card.col = col;
    
    const dbStatusMap = { 'pending': 'pendente', 'progress': 'em_andamento', 'done': 'concluida' };
    const formData = new FormData();
    formData.append('id_tarefa', card.id);
    formData.append('status', dbStatusMap[col]);

    fetch('../tasks/update_status.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(text => {
        if(text.trim() !== "OK") {
            alert("Erro ao atualizar na base de dados:\n" + text);
        }
    })
    .catch(err => alert("Erro de comunicação com o servidor: " + err));
  }
  dragId = null;
  renderAll();
}

renderAll();