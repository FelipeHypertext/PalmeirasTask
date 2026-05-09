/* ─── Data ─────────────────────────────────────────────── */
const AVATAR_CLASSES = ['av-a','av-b','av-c','av-d','av-e','av-f','av-g'];
const TAG_LABELS  = { tecnico:'Tecnico', auxtec:'Auxiliar técnico', prepfi:'Preparador físico', prepgoleiro:'Treinador de goleiros', analista:'Analista de desempenho', coordenador:'Coordenador técnico' };
const PRIORITY    = { high:'pri-high', med:'pri-med', low:'pri-low' };
 
let nextId = 100;
let dragId = null;
 
let cards = [
  { id:1, col:'pending',  title:'Reauxtec da tela de login',   desc:'Atualizar layout seguindo o novo DS',          tag:'auxtec',    priority:'high', date:'12 mai', comments:3, authors:[{i:'AB',c:'av-a'},{i:'CM',c:'av-b'}] },
  { id:2, col:'pending',  title:'Integrar API de pagamentos',  desc:'Gateway Stripe + testes unitários',             tag:'tecnico',       priority:'high', date:'14 mai', comments:5, authors:[{i:'RF',c:'av-c'}] },
  { id:3, col:'pending',  title:'Planejar campanha Q3',        desc:'Briefing e calendário editorial',               tag:'prepfi', priority:'low',  date:'20 mai', comments:1, authors:[{i:'LC',c:'av-d'},{i:'GS',c:'av-e'}] },
  { id:4, col:'progress', title:'Implementar dark mode',       desc:'Tokens CSS + persistência local',               tag:'tecnico',       priority:'med',  date:'11 mai', comments:7, authors:[{i:'AB',c:'av-a'}] },
  { id:5, col:'progress', title:'Testes de regressão v2.4',    desc:'Cobrir fluxos críticos de checkout',            tag:'prepgoleiro',        priority:'high', date:'10 mai', comments:2, authors:[{i:'PL',c:'av-f'},{i:'CM',c:'av-b'}] },
  { id:6, col:'progress', title:'Migrar servidor para k8s',    desc:'Configurar cluster + HPA',                      tag:'analista',     priority:'med',  date:'13 mai', comments:4, authors:[{i:'RF',c:'av-c'}] },
  { id:7, col:'done',     title:'Mapa de jornada do usuário',  desc:'Workshop realizado e doc aprovado',             tag:'coordenador',   priority:'low',  date:'08 mai', comments:6, authors:[{i:'LC',c:'av-d'}] },
  { id:8, col:'done',     title:'Ajuste de performance SQL',   desc:'Índices adicionados, query -70%',               tag:'tecnico',       priority:'med',  date:'07 mai', comments:2, authors:[{i:'AB',c:'av-a'},{i:'GS',c:'av-e'}] },
];
 
/* ─── Render ────────────────────────────────────────────── */
function initials(name) {
  return name.trim().split(' ').map(p => p[0].toUpperCase()).slice(0,2).join('');
}
function avatarClass(name) {
  return AVATAR_CLASSES[name.charCodeAt(0) % AVATAR_CLASSES.length];
}
function today() {
  const d = new Date();
  return d.getDate() + ' ' + d.toLocaleString('pt-BR',{month:'short'}).replace('.','');
}
 
function buildCard(card) {
  const avatarsHtml = card.authors.map(a =>
    `<div class="avatar ${a.c}" title="${a.i}">${a.i}</div>`
  ).join('');
 
  return `
  <div class="card" id="card-${card.id}" draggable="true"
       ondragstart="onDragStart(event,${card.id})"
       ondragend="onDragEnd()">
    <div class="card-top">
      <span class="card-tag tag-${card.tag}">${TAG_LABELS[card.tag]}</span>
      <button class="card-remove" onclick="removeCard(${card.id})" title="Remover tarefa">
        <i class="ti ti-x"></i>
      </button>
    </div>
    <div class="card-title">${escHtml(card.title)}</div>
    <div class="card-desc">${escHtml(card.desc)}</div>
    <div class="card-footer">
      <div class="card-meta">
        <div class="card-date"><i class="ti ti-calendar"></i> ${card.date}</div>
        <div class="card-comments"><i class="ti ti-message"></i> ${card.comments}</div>
        <div class="priority-dot ${PRIORITY[card.priority]}" title="Prioridade ${card.priority}"></div>
      </div>
      <div class="avatars">${avatarsHtml}</div>
    </div>
  </div>`;
}
 
function escHtml(str) {
  return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
 
function renderAll() {
  const cols = ['pending','progress','done'];
  let total = 0;
  cols.forEach(col => {
    const colCards = cards.filter(c => c.col === col);
    const container = document.getElementById('cards-' + col);
    document.getElementById('cnt-' + col).textContent = colCards.length;
    total += colCards.length;
 
    if (colCards.length === 0) {
      container.innerHTML = `
        <div class="col-empty">
          <i class="ti ti-clipboard-list"></i>
          Sem tarefas aqui
        </div>`;
    } else {
      container.innerHTML = colCards.map(buildCard).join('');
    }
  });
 
  const done = cards.filter(c => c.col === 'done').length;
  document.getElementById('sprint-sub').textContent =
    `${total} tarefa${total !== 1 ? 's' : ''} · ${done} concluída${done !== 1 ? 's' : ''}`;
}
 
/* ─── Drag & Drop ───────────────────────────────────────── */
function onDragStart(e, id) {
  dragId = id;
  setTimeout(() => {
    const el = document.getElementById('card-' + id);
    if (el) el.classList.add('dragging');
  }, 0);
}
function onDragEnd() {
  document.querySelectorAll('.card').forEach(el => el.classList.remove('dragging'));
  document.querySelectorAll('.drop-zone').forEach(z => z.classList.remove('active'));
}
function onDragOver(e, col) {
  e.preventDefault();
  document.getElementById('zone-' + col).classList.add('active');
}
function onDragLeave(e) {
  document.querySelectorAll('.drop-zone').forEach(z => z.classList.remove('active'));
}
function onDrop(e, col) {
  e.preventDefault();
  document.querySelectorAll('.drop-zone').forEach(z => z.classList.remove('active'));
  if (dragId === null) return;
  const card = cards.find(c => c.id === dragId);
  if (card) card.col = col;
  dragId = null;
  renderAll();
}
 
/* ─── Remove ────────────────────────────────────────────── */
function removeCard(id) {
  cards = cards.filter(c => c.id !== id);
  renderAll();
}
 
/* ─── Modal ─────────────────────────────────────────────── */
function openModal(col) {
  document.getElementById('m-col').value   = col || 'pending';
  document.getElementById('m-title').value  = '';
  document.getElementById('m-desc').value   = '';
  document.getElementById('m-author').value = '';
  document.getElementById('modal-overlay').classList.add('open');
  setTimeout(() => document.getElementById('m-title').focus(), 50);
}
function closeModal() {
  document.getElementById('modal-overlay').classList.remove('open');
}
 
// Fechar ao clicar fora
document.getElementById('modal-overlay').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});
 
// Fechar com Escape
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
 
function createCard() {
  const title  = document.getElementById('m-title').value.trim();
  const desc   = document.getElementById('m-desc').value.trim();
  const author = document.getElementById('m-author').value.trim();
  const col    = document.getElementById('m-col').value;
  const tag    = document.getElementById('m-tag').value;
  const pri    = document.getElementById('m-pri').value;
 
  if (!title)  { document.getElementById('m-title').focus();  return; }
  if (!author) { document.getElementById('m-author').focus(); return; }
 
  cards.push({
    id:       nextId++,
    col,
    title,
    desc:     desc || 'Sem descrição',
    tag,
    priority: pri,
    date:     today(),
    comments: 0,
    authors:  [{ i: initials(author), c: avatarClass(author) }]
  });
 
  closeModal();
  renderAll();
}
 
/* ─── Init ──────────────────────────────────────────────── */
renderAll();