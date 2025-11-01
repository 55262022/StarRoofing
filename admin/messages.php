<?php
// admin-messenger-style.php
include '../authentication/auth.php';
require_once '../database/starroofing_db.php';

// server-side: fetch initial unaccepted inquiries
$search_term = $_GET['search'] ?? '';
$source = $_GET['source'] ?? 'all'; // 'all', 'form', 'chatbot'
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 50;
$offset = ($page - 1) * $limit;

$sql = "SELECT id, firstname, lastname, email, phone, message, submitted_at, source
        FROM inquiries
        WHERE COALESCE(is_accepted,0) = 0";

$params = [];
$types = '';

// Add source filter
if ($source !== 'all') {
    $sql .= " AND source = ?";
    $params[] = $source;
    $types .= 's';
}

$sql .= " ORDER BY submitted_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();
$inquiries = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin — Messages — Star Roofing</title>

  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    :root{
      --bg: #f6f8fb;
      --card: #ffffff;
      --muted: #6b7280;
      --accent: #1e88ff;
      --accent-600: #1565c0;
      --border: #e6edf6;
      --chatbot-color: #10b981;
      --form-color: #8b5cf6;
      font-family: 'Montserrat', system-ui, -apple-system, sans-serif;
      color: #1f2937;
    }
    *{box-sizing:border-box}
    body{margin:0;background:var(--bg);min-height:100vh}
    .app {
      max-width:1300px;
      margin:18px auto;
      display:flex;
      gap:18px;
      padding:18px;
    }

    /* Left column */
    .left {
      width:360px;
      min-width:280px;
      display:flex;
      flex-direction:column;
      gap:12px;
    }
    .brand {
      display:flex;align-items:center;gap:12px;background:var(--card);padding:12px;border-radius:12px;border:1px solid var(--border);
    }
    .brand img{width:44px;height:44px;border-radius:8px;object-fit:cover}
    .brand h1{font-size:16px;margin:0}
    .brand .muted{color:var(--muted);font-size:13px}

    /* Source Filter Tabs */
    .source-tabs {
      display:flex;
      gap:8px;
      background:var(--card);
      padding:8px;
      border-radius:12px;
      border:1px solid var(--border);
    }
    .source-tab {
      flex:1;
      padding:8px 12px;
      border:none;
      background:transparent;
      color:var(--muted);
      cursor:pointer;
      border-radius:8px;
      font-weight:600;
      font-size:13px;
      transition:all 0.2s;
      display:flex;
      align-items:center;
      justify-content:center;
      gap:6px;
    }
    .source-tab:hover{
      background:rgba(30, 136, 255, 0.1);
      color:var(--accent);
    }
    .source-tab.active{
      background:var(--accent);
      color:white;
    }
    .source-tab.chatbot-tab.active{
      background:var(--chatbot-color);
    }
    .source-tab.form-tab.active{
      background:var(--form-color);
    }

    .search-bar {
      display:flex;gap:8px;padding:10px;background:var(--card);border-radius:12px;border:1px solid var(--border);align-items:center;
    }
    .search-bar input{
      flex:1;padding:10px;border-radius:10px;border:1px solid #eef6ff;background:#fbfdff;font-size:14px;
    }
    .search-bar button{
      background:transparent;border:0;color:var(--muted);font-size:16px;padding:6px;cursor:pointer;
    }

    .panel {
      background:var(--card);border-radius:12px;padding:10px;border:1px solid var(--border);overflow:auto;
      max-height:72vh;
    }

    .section-title {
      display:flex;justify-content:space-between;align-items:center;padding:6px 8px;color:var(--muted);font-weight:600;font-size:13px;
    }

    .list {
      display:flex;flex-direction:column;gap:6px;padding:6px;
    }
    .item {
      display:flex;gap:10px;padding:10px;border-radius:10px;align-items:center;cursor:pointer;
      transition:background .12s, transform .06s;
      position:relative;
    }
    .item:hover{background:#f4f9ff}
    .avatar {width:44px;height:44px;border-radius:8px;background:linear-gradient(180deg,#dbeafe,#bfdbfe);display:flex;align-items:center;justify-content:center;font-weight:700;color:#08306b}
    .meta {flex:1;min-width:0}
    .meta .name {font-weight:700;font-size:14px}
    .meta .snippet {font-size:13px;color:var(--muted);margin-top:6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .meta .contact {font-size:12px;color:var(--muted);margin-top:6px}
    .right-meta {display:flex;flex-direction:column;align-items:flex-end;gap:6px}
    .time {font-size:12px;color:var(--muted)}

    /* Source Badge */
    .source-badge {
      position:absolute;
      top:8px;
      left:8px;
      padding:3px 8px;
      border-radius:12px;
      font-size:10px;
      font-weight:700;
      text-transform:uppercase;
      letter-spacing:0.5px;
    }
    .source-badge.chatbot {
      background:rgba(16, 185, 129, 0.15);
      color:var(--chatbot-color);
      border:1px solid rgba(16, 185, 129, 0.3);
    }
    .source-badge.form {
      background:rgba(139, 92, 246, 0.15);
      color:var(--form-color);
      border:1px solid rgba(139, 92, 246, 0.3);
    }

    .accept-btn {
      background:transparent;border:1px solid #dbeefe;padding:6px 8px;border-radius:8px;color:var(--accent);font-weight:700;cursor:pointer;
      transition:all 0.2s;
    }
    .accept-btn:hover{
      background:var(--accent);
      color:white;
    }

    /* Chat column */
    .chat {
      flex:1;display:flex;flex-direction:column;gap:12px;
    }
    .chat-header {
      display:flex;align-items:center;justify-content:space-between;padding:12px;border-radius:12px;background:var(--card);border:1px solid var(--border);
    }
    .chat-header .left {display:flex;gap:12px;align-items:center}
    .chat-header .title {font-weight:700}
    .chat-window {
      background:var(--card);border-radius:12px;padding:16px;border:1px solid var(--border);overflow:auto;flex:1;max-height:72vh;
      display:flex;flex-direction:column;gap:10px;
    }

    .bubble {
      max-width:72%;padding:12px;border-radius:12px;line-height:1.4;position:relative;
      box-shadow:0 4px 10px rgba(23,35,49,0.04);
    }
    .bubble .author {font-size:12px;color:var(--muted);margin-bottom:6px}
    .bubble .time {font-size:11px;color:var(--muted);margin-top:8px;text-align:right}
    .from-admin {
      align-self:flex-end;background:linear-gradient(180deg,#e8f0ff,#dbeafe);border:1px solid #d6e9ff;border-bottom-right-radius:4px;
    }
    .from-client {
      align-self:flex-start;background:#f6f7fb;border:1px solid #eef0f5;border-bottom-left-radius:4px;
    }

    .chat-footer {
      display:flex;gap:10px;align-items:center;padding:10px;border-radius:12px;background:var(--card);border:1px solid var(--border);
    }
    .chat-footer textarea {
      flex:1;min-height:56px;padding:12px;border-radius:10px;border:1px solid #eef4ff;resize:vertical;font-size:14px;
    }
    .btn-send {
      background:var(--accent);color:#fff;border:0;padding:12px 16px;border-radius:10px;font-weight:700;cursor:pointer;
    }

    .empty {
      padding:26px;text-align:center;color:var(--muted);border-radius:8px;background:linear-gradient(180deg,#ffffff,#fbfdff);
    }

    @media (max-width: 980px){
      .app{padding:12px;flex-direction:column}
      .left{width:100%}
      .chat{width:100%}
      .source-tabs{flex-direction:column}
    }
  </style>
</head>
<body>
  <div class="app" role="application">
    <div class="left" aria-label="Left sidebar">
      <div class="brand">
        <img src="../assets/logo.png" alt="Logo">
        <div>
          <h1>Star Roofing</h1>
          <div class="muted">Admin — Messages</div>
        </div>
      </div>

      <!-- Source Filter Tabs -->
      <div class="source-tabs">
        <button class="source-tab active" data-source="all" id="tab-all">
          <i class="fas fa-inbox"></i> All
        </button>
        <button class="source-tab form-tab" data-source="form" id="tab-form">
          <i class="fas fa-file-alt"></i> Inquiries
        </button>
        <button class="source-tab chatbot-tab" data-source="chatbot" id="tab-chatbot">
          <i class="fas fa-comment-dots"></i> Chats
        </button>
      </div>

      <div class="search-bar" role="search">
        <form id="searchForm" style="flex:1;display:flex;gap:8px;">
          <input id="searchInput" name="search" placeholder="Search name, email or message..." value="<?= htmlspecialchars($search_term) ?>">
          <button type="submit" title="Search"><i class="fas fa-search"></i></button>
        </form>
        <button id="refreshBtn" title="Refresh"><i class="fas fa-sync"></i></button>
      </div>

      <div class="panel" id="leftPanel">
        <div class="section-title"><span>Conversations</span><span class="muted" id="convCount">0</span></div>
        <div class="list" id="conversationsList" aria-live="polite">
          <div class="empty" id="convEmpty">No conversations yet. Accept an inquiry to start.</div>
        </div>

        <hr style="border:none;height:1px;background:var(--border);margin:12px 0;border-radius:2px">

        <div class="section-title">
          <span>New Inquiries</span>
          <span class="muted" id="newCount"><?= count($inquiries) ?></span>
        </div>
        <div class="list" id="inquiriesList" aria-live="polite">
          <?php foreach ($inquiries as $inq): ?>
            <div class="item" data-id="<?= $inq['id'] ?>">
              <?php if (isset($inq['source']) && $inq['source'] !== 'form'): ?>
                <span class="source-badge <?= htmlspecialchars($inq['source']) ?>">
                  <?= $inq['source'] === 'chatbot' ? '💬 Chat' : htmlspecialchars($inq['source']) ?>
                </span>
              <?php endif; ?>
              <div class="avatar"><?= strtoupper(substr($inq['firstname'],0,1) . substr($inq['lastname'],0,1)) ?></div>
              <div class="meta">
                <div class="name"><?= htmlspecialchars($inq['firstname'].' '.$inq['lastname']) ?></div>
                <div class="snippet"><?= htmlspecialchars(mb_strimwidth($inq['message'],0,80,'...')) ?></div>
                <div class="contact"><?= htmlspecialchars($inq['email']) ?> · <?= htmlspecialchars($inq['phone']) ?></div>
              </div>
              <div class="right-meta">
                <div class="time"><?= date('M j, g:ia', strtotime($inq['submitted_at'])) ?></div>
                <button class="accept-btn" onclick="event.stopPropagation(); acceptInquiry(<?= $inq['id'] ?>, this)">Accept</button>
              </div>
            </div>
          <?php endforeach; ?>
          <?php if (count($inquiries) === 0): ?>
            <div class="empty">No new inquiries. They will appear here when clients submit them.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="chat" aria-label="Chat area">
      <div class="chat-header" role="banner">
        <div style="display:flex;align-items:center;gap:12px">
          <div class="avatar" id="chatAvatar">SR</div>
          <div>
            <div class="title" id="chatTitle">Select a conversation</div>
            <div class="muted" id="chatSubtitle">Accepted inquiries open conversations here</div>
          </div>
        </div>
        <div>
          <!-- empty placeholder for action icons -->
        </div>
      </div>

      <div class="chat-window" id="chatWindow" tabindex="0">
        <div class="empty" id="chatEmpty">Open a conversation to view messages</div>
      </div>

      <div class="chat-footer" id="chatFooter" style="display:none">
        <textarea id="replyInput" placeholder="Write your reply..."></textarea>
        <button class="btn-send" id="sendBtn">Send</button>
      </div>
    </div>
  </div>

<script>
// Current filter state
let currentSource = 'all';

// Create audio context for notifications
let audioContext = null;
function playNotificationSound() {
  try {
    if (!audioContext) {
      audioContext = new (window.AudioContext || window.webkitAudioContext)();
    }
    
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();
    
    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);
    
    oscillator.type = 'sine';
    oscillator.frequency.setValueAtTime(660, audioContext.currentTime);
    gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
    
    oscillator.start(audioContext.currentTime);
    oscillator.stop(audioContext.currentTime + 0.1);
    
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);
  } catch(e) {
    console.error('Error playing notification:', e);
  }
}

const dom = (s, ctx=document) => ctx.querySelector(s);
const qsa = (s, ctx=document) => Array.from(ctx.querySelectorAll(s));
const escapeHtml = s => s ? String(s).replace(/[&<>"'`]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;',"`":'&#96;'})[c]) : '';

let acceptedThreads = {};
let currentThreadId = null;
let pollingTimer = null;

// Tab switching
qsa('.source-tab').forEach(tab => {
  tab.addEventListener('click', function() {
    qsa('.source-tab').forEach(t => t.classList.remove('active'));
    this.classList.add('active');
    currentSource = this.dataset.source;
    loadInquiries();
  });
});

function formatDate(iso){
  if(!iso) return '';
  const d = new Date(iso);
  return d.toLocaleString();
}

function getSourceBadgeHTML(source) {
  if (!source || source === 'form') return '';
  const label = source === 'chatbot' ? '💬 Chat' : source;
  return `<span class="source-badge ${source}">${escapeHtml(label)}</span>`;
}

function createConversationItem(inq){
  const el = document.createElement('div');
  el.className = 'item';
  el.dataset.id = inq.id;
  el.innerHTML = `
    ${getSourceBadgeHTML(inq.source)}
    <div class="avatar">${escapeHtml((inq.firstname||'')[0] + (inq.lastname||'')[0] || 'SR')}</div>
    <div class="meta">
      <div class="name">${escapeHtml(inq.firstname + ' ' + inq.lastname)}</div>
      <div class="snippet">${escapeHtml(inq.message.length>60? inq.message.substr(0,60)+'…': inq.message)}</div>
      <div class="contact">${escapeHtml(inq.email || '')} · ${escapeHtml(inq.phone || '')}</div>
    </div>
    <div class="right-meta">
      <div class="time">${formatDate(inq.submitted_at)}</div>
    </div>
  `;
  el.addEventListener('click', ()=> openThread(inq.id));
  return el;
}

function updateCounts(){
  const convCount = Object.keys(acceptedThreads).length;
  dom('#convCount').textContent = convCount;
}

function attachInitialListeners(){
  qsa('#inquiriesList .item').forEach(it => {
    it.addEventListener('click', ()=> {
      const id = it.dataset.id;
      openThreadPreview(id);
    });
  });
}
attachInitialListeners();

async function loadInquiries(){
  try {
    const searchTerm = encodeURIComponent(dom('#searchInput').value||'');
    const response = await fetch(`messages-API/fetch_inquiries.php?search=${searchTerm}&source=${currentSource}`);
    const data = await response.json();
    if(!data.success){
      console.error('fetch_inquiries error', data);
      return;
    }

    const list = dom('#inquiriesList');
    list.innerHTML = '';
    const arr = data.inquiries || [];
    dom('#newCount').textContent = arr.length;

    if(arr.length === 0){
      const sourceText = currentSource === 'all' ? 'inquiries' : 
                        currentSource === 'chatbot' ? 'chatbot inquiries' : 'contact form inquiries';
      list.innerHTML = `<div class="empty">No new ${sourceText}. They will appear here when clients submit them.</div>`;
      return;
    }

    arr.forEach(i=>{
      const item = document.createElement('div');
      item.className = 'item';
      item.dataset.id = i.id;
      item.innerHTML = `
        ${getSourceBadgeHTML(i.source)}
        <div class="avatar">${escapeHtml((i.firstname||'')[0] + (i.lastname||'')[0] || 'SR')}</div>
        <div class="meta">
          <div class="name">${escapeHtml(i.firstname + ' ' + i.lastname)}</div>
          <div class="snippet">${escapeHtml(i.message.length>80? i.message.substr(0,80)+'…': i.message)}</div>
          <div class="contact">${escapeHtml(i.email)} · ${escapeHtml(i.phone || '')}</div>
        </div>
        <div class="right-meta">
          <div class="time">${formatDate(i.submitted_at)}</div>
          <button class="accept-btn">Accept</button>
        </div>
      `;
      const acceptBtn = item.querySelector('.accept-btn');
      acceptBtn.addEventListener('click', (ev)=>{
        ev.stopPropagation();
        acceptInquiry(i.id, acceptBtn);
      });
      item.addEventListener('click', ()=> openThreadPreview(i.id));
      list.appendChild(item);
    });

  } catch (e) {
    console.error('loadInquiries error', e);
  }
}

async function acceptInquiry(id, btnEl){
  try {
    btnEl && (btnEl.disabled = true);
    const res = await fetch('messages-API/accept_inquiry_new.php', {
      method:'POST',
      headers: {'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams({id})
    });
    const j = await res.json();
    if(j.success){
      const thread = await fetchThreadData(id);
      if(thread && thread.inquiry){
        acceptedThreads[id] = thread;
        renderConversations();
        openThread(id);
        const el = document.querySelector(`#inquiriesList .item[data-id="${id}"]`);
        if(el) el.remove();
        const remaining = qsa('#inquiriesList .item').length;
        dom('#newCount').textContent = remaining;
      }
    } else {
      alert(j.message || 'Could not accept inquiry.');
    }
  } catch (e){
    alert('Network error.');
    console.error(e);
  } finally {
    btnEl && (btnEl.disabled = false);
  }
}

async function fetchThreadData(id){
  try {
    const res = await fetch(`messages-API/fetch_thread_new.php?id=${id}`);
    const j = await res.json();
    if(!j.success) {
      console.error('fetch_thread error', j);
      return null;
    }
    return j;
  } catch(e){
    console.error('fetchThreadData error', e);
    return null;
  }
}

function renderConversations(){
  const convList = dom('#conversationsList');
  convList.innerHTML = '';
  const keys = Object.keys(acceptedThreads);
  
  if(keys.length === 0){
    convList.innerHTML = '<div class="empty" id="convEmpty">No conversations yet. Accept an inquiry to start.</div>';
    dom('#convCount').textContent = '0';
    return;
  }
  
  const emptyEl = dom('#convEmpty');
  if(emptyEl) emptyEl.remove();
  
  const sortedKeys = keys.sort((a,b)=>{
    const ra = acceptedThreads[a].replies.length ? acceptedThreads[a].replies[acceptedThreads[a].replies.length-1].sent_at : acceptedThreads[a].inquiry.submitted_at;
    const rb = acceptedThreads[b].replies.length ? acceptedThreads[b].replies[acceptedThreads[b].replies.length-1].sent_at : acceptedThreads[b].inquiry.submitted_at;
    return new Date(rb) - new Date(ra);
  });
  
  sortedKeys.forEach(k => {
    const inq = acceptedThreads[k].inquiry;
    const el = createConversationItem(inq);
    convList.appendChild(el);
  });
  
  updateCounts();
}

async function openThread(id){
  try {
    currentThreadId = id;
    let hasNewMessages = false;
    
    if(acceptedThreads[id]) {
      const currentMsgCount = acceptedThreads[id].replies?.length || 0;
      const fresh = await fetchThreadData(id);
      if(!fresh) return;
      const newMsgCount = fresh.replies?.length || 0;
      hasNewMessages = newMsgCount > currentMsgCount;
      acceptedThreads[id] = fresh;
    } else {
      const fresh = await fetchThreadData(id);
      if(!fresh) return;
      acceptedThreads[id] = fresh;
    }

    const thread = acceptedThreads[id];
    if(!thread) return;
    
    if(hasNewMessages && document.hasFocus()) {
      playNotificationSound();
    }
    
    dom('#chatTitle').textContent = thread.inquiry.firstname + ' ' + thread.inquiry.lastname;
    const sourceLabel = thread.inquiry.source === 'chatbot' ? ' (Chatbot)' : '';
    dom('#chatSubtitle').textContent = thread.inquiry.email + ' · ' + (thread.inquiry.phone || '') + sourceLabel;
    dom('#chatAvatar').textContent = (thread.inquiry.firstname||'')[0] + (thread.inquiry.lastname||'')[0];

    const win = dom('#chatWindow');
    const wasAtBottom = win.scrollHeight - win.clientHeight <= win.scrollTop + 1;
    win.innerHTML = '';

    const initial = {
      sender: 'client',
      message: thread.inquiry.message,
      created_at: thread.inquiry.submitted_at,
      author: thread.inquiry.firstname + ' ' + thread.inquiry.lastname
    };

    const all = [initial, ...(thread.replies || []).map(r => ({
      sender: r.sender,
      message: r.message,
      created_at: r.sent_at,
      author: r.sender === 'admin' ? 'Admin' : thread.inquiry.firstname
    }))];

    all.forEach(m => {
      const b = document.createElement('div');
      b.className = 'bubble ' + (m.sender === 'admin' ? 'from-admin' : 'from-client');
      b.innerHTML = `
        <div class="author">${escapeHtml(m.author)}</div>
        <div class="text">${escapeHtml(m.message).replace(/\n/g,'<br>')}</div>
        <div class="time">${formatDate(m.created_at)}</div>
      `;
      win.appendChild(b);
    });

    if (wasAtBottom || hasNewMessages) {
      win.scrollTop = win.scrollHeight;
    }

    dom('#chatFooter').style.display = 'flex';
    const emptyEl = dom('#chatEmpty');
    if(emptyEl) {
      emptyEl.style.display = 'none';
    }

    qsa('.item').forEach(it => it.classList.remove('active'));
    const leftSel = document.querySelector(`.item[data-id="${id}"]`);
    if(leftSel) {
      leftSel.classList.add('active');
    }

    renderConversations();

  } catch(error) {
    console.error('Error opening thread:', error);
  }
}

async function openThreadPreview(id){
  const data = await fetchThreadData(id);
  if(!data) return;
  
  currentThreadId = null;
  dom('#chatTitle').textContent = data.inquiry.firstname + ' ' + data.inquiry.lastname + ' (Preview)';
  const sourceLabel = data.inquiry.source === 'chatbot' ? ' • Chatbot' : '';
  dom('#chatSubtitle').textContent = data.inquiry.email + ' · ' + (data.inquiry.phone || '') + sourceLabel;
  dom('#chatAvatar').textContent = (data.inquiry.firstname||'')[0] + (data.inquiry.lastname||'')[0];

  const win = dom('#chatWindow');
  win.innerHTML = '';

  const initial = {
    sender: 'client',
    message: data.inquiry.message,
    created_at: data.inquiry.submitted_at,
    author: data.inquiry.firstname + ' ' + data.inquiry.lastname
  };
  
  const b = document.createElement('div');
  b.className = 'bubble from-client';
  b.innerHTML = `
    <div class="author">${escapeHtml(initial.author)}</div>
    <div class="text">${escapeHtml(initial.message).replace(/\n/g,'<br>')}</div>
    <div class="time">${formatDate(initial.created_at)}</div>
  `;
  win.appendChild(b);

  const ctaWrap = document.createElement('div');
  ctaWrap.style.marginTop = '12px';
  ctaWrap.innerHTML = `
    <div style="display:flex;gap:8px;align-items:center">
      <button id="previewAccept" class="accept-btn">Accept & Open Conversation</button>
    </div>
  `;
  win.appendChild(ctaWrap);
  
  const btn = dom('#previewAccept');
  btn.addEventListener('click', ()=> {
    const incomingBtn = document.querySelector(`#inquiriesList .item[data-id="${id}"] .accept-btn`);
    acceptInquiry(id, incomingBtn || btn);
  });

  dom('#chatFooter').style.display = 'none';
}

// Send reply
dom('#sendBtn').addEventListener('click', async ()=>{
  const msg = dom('#replyInput').value.trim();
  if(!msg || !currentThreadId) return;
  dom('#sendBtn').disabled = true;
  try {
    const res = await fetch('messages-API/send_reply.php', {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams({inquiry_id: currentThreadId, message: msg})
    });
    const j = await res.json();
    if(j.success){
      acceptedThreads[currentThreadId] = j;
      dom('#replyInput').value = '';
      openThread(currentThreadId);
    } else {
      alert(j.message || 'Could not send reply.');
    }
  } catch(e){
    alert('Network error.');
    console.error(e);
  } finally {
    dom('#sendBtn').disabled = false;
  }
});

// Enter key to send
dom('#replyInput').addEventListener('keypress', (e) => {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault();
    dom('#sendBtn').click();
  }
});

// UI wiring
dom('#searchForm').addEventListener('submit', e => { e.preventDefault(); loadInquiries(); });
dom('#refreshBtn').addEventListener('click', () => { loadInquiries(); });

// Polling & init
loadInquiries();

// Function to update current conversation
async function updateCurrentConversation() {
  if (!currentThreadId) return;
  try {
    const fresh = await fetchThreadData(currentThreadId);
    if (!fresh) return;

    const currentMsgCount = acceptedThreads[currentThreadId]?.replies?.length || 0;
    const newMsgCount = fresh.replies?.length || 0;
    
    if (newMsgCount > currentMsgCount) {
      acceptedThreads[currentThreadId] = fresh;
      renderConversations();
      await openThread(currentThreadId);
    }
  } catch (error) {
    console.error('Error updating conversation:', error);
  }
}

// Start polling intervals
pollingTimer = setInterval(loadInquiries, 5000);
setInterval(updateCurrentConversation, 3000);

// Restore previous session state
(function restoreFromSession(){
  try {
    const raw = sessionStorage.getItem('acceptedThreads_v1');
    if(raw){
      const parsed = JSON.parse(raw);
      acceptedThreads = parsed || {};
      renderConversations();
      
      const lastActiveId = sessionStorage.getItem('lastActiveThread');
      if(lastActiveId && acceptedThreads[lastActiveId]) {
        openThread(lastActiveId);
      }
    }
    
    // Restore active tab
    const savedSource = sessionStorage.getItem('activeSource');
    if(savedSource && savedSource !== 'all') {
      currentSource = savedSource;
      qsa('.source-tab').forEach(t => t.classList.remove('active'));
      const tab = document.querySelector(`.source-tab[data-source="${savedSource}"]`);
      if(tab) tab.classList.add('active');
    }
  } catch(e){
    console.error('Error restoring session:', e);
  }
})();

// Persist acceptedThreads and current thread to sessionStorage on unload
window.addEventListener('beforeunload', ()=>{
  try {
    sessionStorage.setItem('acceptedThreads_v1', JSON.stringify(acceptedThreads));
    if(currentThreadId) {
      sessionStorage.setItem('lastActiveThread', currentThreadId);
    }
    sessionStorage.setItem('activeSource', currentSource);
  } catch(e){
    console.error('Error saving session:', e);
  }
});
</script>
</body>
</html>