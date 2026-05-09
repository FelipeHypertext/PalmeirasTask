<?php
    session_start();
    require_once("../config/db.php");
    require_once("../includes/session_check.php");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Palmeiras Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" />
  <link rel="stylesheet" href="style.css">
  <script src="main.js" defer></script>
</head>
<body>
 
<!-- Topbar -->
<header class="topbar">
  <div class="topbar-brand">
    <i class="ti ti-layout-kanban"></i>
    Palmeiras Board
  </div>
  <div class="topbar-actions">
    <button class="btn" onclick="openModal('pending')">
      <i class="ti ti-plus"></i> Nova tarefa
    </button>
  </div>
</header>
 
<!-- Page -->
<main class="page">
  <div class="page-header">
    <div>
      <div class="page-title">Gerenciador de tarefas</div>
      <div class="page-sub" id="sprint-sub">Carregando tarefas…</div>
    </div>
  </div>
 
  <!-- Board -->
  <div class="board">
 
    <!-- Pendente -->
    <div class="col" id="col-pending"
         ondragover="onDragOver(event,'pending')"
         ondrop="onDrop(event,'pending')"
         ondragleave="onDragLeave(event)">
      <div class="col-header">
        <div class="col-label">
          <span class="col-dot dot-pending"></span>
          Pendente
        </div>
        <div style="display:flex;align-items:center;gap:6px">
          <span class="col-badge badge-pending" id="cnt-pending">0</span>
          <button class="col-add-btn" onclick="openModal('pending')" title="Adicionar tarefa">
            <i class="ti ti-plus"></i>
          </button>
        </div>
      </div>
      <div class="drop-zone" id="zone-pending"></div>
      <div id="cards-pending"></div>
    </div>
 
    <!-- Em andamento -->
    <div class="col" id="col-progress"
         ondragover="onDragOver(event,'progress')"
         ondrop="onDrop(event,'progress')"
         ondragleave="onDragLeave(event)">
      <div class="col-header">
        <div class="col-label">
          <span class="col-dot dot-progress"></span>
          Em andamento
        </div>
        <div style="display:flex;align-items:center;gap:6px">
          <span class="col-badge badge-progress" id="cnt-progress">0</span>
          <button class="col-add-btn" onclick="openModal('progress')" title="Adicionar tarefa">
            <i class="ti ti-plus"></i>
          </button>
        </div>
      </div>
      <div class="drop-zone" id="zone-progress"></div>
      <div id="cards-progress"></div>
    </div>
 
    <!-- Concluída -->
    <div class="col" id="col-done"
         ondragover="onDragOver(event,'done')"
         ondrop="onDrop(event,'done')"
         ondragleave="onDragLeave(event)">
      <div class="col-header">
        <div class="col-label">
          <span class="col-dot dot-done"></span>
          Concluída
        </div>
        <div style="display:flex;align-items:center;gap:6px">
          <span class="col-badge badge-done" id="cnt-done">0</span>
          <button class="col-add-btn" onclick="openModal('done')" title="Adicionar tarefa">
            <i class="ti ti-plus"></i>
          </button>
        </div>
      </div>
      <div class="drop-zone" id="zone-done"></div>
      <div id="cards-done"></div>
    </div>
 
  </div>
</main>
 
<!-- Modal nova tarefa -->
<div class="modal-overlay" id="modal-overlay">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Nova tarefa</span>
      <button class="modal-close" onclick="closeModal()">
        <i class="ti ti-x"></i>
      </button>
    </div>
    <div class="modal-body">
      <input  id="m-title"  class="form-input"  type="text" placeholder="Título da tarefa *" />
      <input  id="m-desc"   class="form-input"  type="text" placeholder="Descrição breve" />
      <input  id="m-author" class="form-input"  type="text" placeholder="Seu nome *" />
      <div class="form-row">
        <select id="m-col" class="form-select">
          <option value="pending">Pendente</option>
          <option value="progress">Em andamento</option>
          <option value="done">Concluída</option>
        </select>
        <select id="m-tag" class="form-select">
          <option value="tecnico">Técnico</option>
          <option value="auxtec">Auxiliar técnico</option>
          <option value="prepfi">Preparador físico</option>
          <option value="prepgoleiro">Treinador de goleiros</option>
          <option value="analista">Analista de desempenho</option>
          <option value="coordenador">Coordenador técnico</option>
        </select>
        <select id="m-pri" class="form-select">
          <option value="high">Alta</option>
          <option value="med">Média</option>
          <option value="low">Baixa</option>
        </select>
      </div>
      <button class="modal-submit" onclick="createCard()">Criar tarefa</button>
    </div>
  </div>
</div>
</body>
</html>