<?php
// admin-messenger-style.php
// Drop-in UI file. Keeps same endpoints under messages-API/*
// Make sure this file is served from same directory context as your previous UI (so paths to messages-API/* remain valid).

include '../authentication/auth.php';
require_once '../database/starroofing_db.php';

// server-side: fetch initial unaccepted inquiries (same SQL as you provided)
$search_term = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 50;
$offset = ($page - 1) * $limit;

$sql = "SELECT id, firstname, lastname, email, phone, message, submitted_at
        FROM inquiries
        WHERE COALESCE(is_accepted,0) = 0
        ORDER BY submitted_at DESC
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $limit, $offset);
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
    }
    .item:hover{background:#f4f9ff}
    .avatar {width:44px;height:44px;border-radius:8px;background:linear-gradient(180deg,#dbeafe,#bfdbfe);display:flex;align-items:center;justify-content:center;font-weight:700;color:#08306b}
    .meta {flex:1;min-width:0}
    .meta .name {font-weight:700;font-size:14px}
    .meta .snippet {font-size:13px;color:var(--muted);margin-top:6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .meta .contact {font-size:12px;color:var(--muted);margin-top:6px}
    .right-meta {display:flex;flex-direction:column;align-items:flex-end;gap:6px}
    .time {font-size:12px;color:var(--muted)}

    .accept-btn {
      background:transparent;border:1px solid #dbeefe;padding:6px 8px;border-radius:8px;color:var(--accent);font-weight:700;cursor:pointer;
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
          <!-- accepted threads injected here -->
          <div class="empty" id="convEmpty">No conversations yet. Accept an inquiry to start.</div>
        </div>

        <hr style="border:none;height:1px;background:var(--border);margin:12px 0;border-radius:2px">

        <div class="section-title"><span>New Inquiries</span><span class="muted" id="newCount"><?= count($inquiries) ?></span></div>
        <div class="list" id="inquiriesList" aria-live="polite">
          <?php foreach ($inquiries as $inq): ?>
            <div class="item" data-id="<?= $inq['id'] ?>">
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
/*
  Front-end behavior:
   - Polls messages-API/fetch_inquiries.php for new inquiries (every 5s)
   - When admin accepts: POST to messages-API/accept_inquiry.php, add to Conversations list and open the thread
   - Conversations are kept in-page (memory) so they remain visible in left sidebar while admin is on page
   - Clicking an item opens messages via messages-API/fetch_thread.php
   - Send reply via messages-API/send_reply.php
*/

const dom = (s, ctx=document) => ctx.querySelector(s);
const qsa = (s, ctx=document) => Array.from(ctx.querySelectorAll(s));
const escapeHtml = s => s ? String(s).replace(/[&<>"'`]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;',"`":'&#96;'})[c]) : '';

let acceptedThreads = {}; // in-memory store: id -> {inquiry, replies}
let currentThreadId = null;
let pollingTimer = null;

// ---------- helpers ----------
function formatDate(iso){
  if(!iso) return '';
  const d = new Date(iso);
  return d.toLocaleString();
}
function createConversationItem(inq){
  const el = document.createElement('div');
  el.className = 'item';
  el.dataset.id = inq.id;
  el.innerHTML = `
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
  // new count left as dynamic from server fetch
}

// ---------- load initial inquiries (server-side already rendered) ----------
function attachInitialListeners(){
  qsa('#inquiriesList .item').forEach(it => {
    it.addEventListener('click', ()=> {
      const id = it.dataset.id;
      // clicking a new inquiry opens a preview (we choose to accept to open full conversation)
      openThreadPreview(id);
    });
  });
  // attach accept buttons (already inline onclick used, but re-bind to be safe)
  qsa('#inquiriesList .accept-btn').forEach(btn=>{
    // they already have onclick attribute; keep it.
  });
}
attachInitialListeners();

// ---------- fetch new inquiries ----------
async function loadInquiries(){
  try {
    const searchTerm = encodeURIComponent(dom('#searchInput').value||'');
    const response = await fetch(`messages-API/fetch_inquiries.php?search=${searchTerm}`);
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
      list.innerHTML = '<div class="empty">No new inquiries. They will appear here when clients submit them.</div>';
      return;
    }

    arr.forEach(i=>{
      const item = document.createElement('div');
      item.className = 'item';
      item.dataset.id = i.id;
      item.innerHTML = `
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

// ---------- Accept Inquiry ----------
async function acceptInquiry(id, btnEl){
  try {
    btnEl && (btnEl.disabled = true);
    const res = await fetch('messages-API/accept_inquiry.php', {
      method:'POST',
      headers: {'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams({id})
    });
    const j = await res.json();
    if(j.success){
      // fetch thread and add to conversations
      const thread = await fetchThreadData(id);
      if(thread && thread.inquiry){
        acceptedThreads[id] = thread;
        renderConversations();
        openThread(id); // open immediately
        // remove from new inquiries list visually (if present)
        const el = document.querySelector(`#inquiriesList .item[data-id="${id}"]`);
        if(el) el.remove();
        // update new count
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

// ---------- Fetch thread data ----------
async function fetchThreadData(id){
  try {
    const res = await fetch(`messages-API/fetch_thread.php?id=${id}`);
    const j = await res.json();
    if(!j.success) {
      console.error('fetch_thread error', j);
      return null;
    }
    return j; // {success,inquiry,replies}
  } catch(e){
    console.error('fetchThreadData error', e);
    return null;
  }
}

// ---------- Render conversations list ----------
function renderConversations(){
  const convList = dom('#conversationsList');
  convList.innerHTML = '';
  const keys = Object.keys(acceptedThreads);
  if(keys.length === 0){
    // Create empty state if no conversations
    convList.innerHTML = '<div class="empty" id="convEmpty">No conversations yet. Accept an inquiry to start.</div>';
    dom('#convCount').textContent = '0';
    return;
  }
  
  // Remove any empty state that might exist
  const emptyEl = dom('#convEmpty');
  if(emptyEl) emptyEl.remove();
  
  keys.sort((a,b)=>{
    // optional: most recent message first
    const ra = acceptedThreads[a].replies.length ? acceptedThreads[a].replies[acceptedThreads[a].replies.length-1].sent_at : acceptedThreads[a].inquiry.submitted_at;
    const rb = acceptedThreads[b].replies.length ? acceptedThreads[b].replies[acceptedThreads[b].replies.length-1].sent_at : acceptedThreads[b].inquiry.submitted_at;
    return new Date(rb) - new Date(ra);
  }).forEach(k=>{
    const inq = acceptedThreads[k].inquiry;
    const el = createConversationItem(inq);
    convList.appendChild(el);
  });
  updateCounts();
}

// ---------- Open a conversation (load messages into right pane) ----------
async function openThread(id){
  currentThreadId = id;
  // ensure thread present in acceptedThreads; if not, fetch and add
  if(!acceptedThreads[id]){
    const data = await fetchThreadData(id);
    if(!data) return;
    acceptedThreads[id] = data;
    renderConversations();
  } else {
    // refresh replies by fetching latest
    const fresh = await fetchThreadData(id);
    if(fresh) {
      acceptedThreads[id] = fresh;
    }
  }

  const thread = acceptedThreads[id];
  if(!thread) return;
  // update header
  dom('#chatTitle').textContent = thread.inquiry.firstname + ' ' + thread.inquiry.lastname;
  dom('#chatSubtitle').textContent = thread.inquiry.email + ' · ' + (thread.inquiry.phone || '');
  dom('#chatAvatar').textContent = (thread.inquiry.firstname||'')[0] + (thread.inquiry.lastname||'')[0];

  // render messages
  const win = dom('#chatWindow');
  win.innerHTML = '';

  // first message (initial inquiry)
  const initial = {
    sender: 'client',
    message: thread.inquiry.message,
    created_at: thread.inquiry.submitted_at,
    author: thread.inquiry.firstname + ' ' + thread.inquiry.lastname
  };

  const all = [initial, ...(thread.replies || []).map(r=>({sender:r.sender, message:r.message, created_at:r.sent_at, author: r.sender === 'admin' ? 'Admin' : thread.inquiry.firstname}))];

  all.forEach(m=>{
    const b = document.createElement('div');
    b.className = 'bubble ' + (m.sender === 'admin' ? 'from-admin' : 'from-client');
    b.innerHTML = `<div class="author">${escapeHtml(m.author)}</div>
                   <div class="text">${escapeHtml(m.message).replace(/\n/g,'<br>')}</div>
                   <div class="time">${formatDate(m.created_at)}</div>`;
    win.appendChild(b);
  });

  win.scrollTop = win.scrollHeight;
  dom('#chatFooter').style.display = 'flex';
  dom('#chatEmpty') && (dom('#chatEmpty').style.display = 'none');

  // highlight selected in left list
  qsa('.item').forEach(it => it.classList.remove('active'));
  const leftSel = document.querySelector(`.item[data-id="${id}"]`);
  leftSel && leftSel.classList.add('active');
}

// Preview opening a new inquiry (not accepted yet) - shows the details but prompts to accept
async function openThreadPreview(id){
  // load inquiry details from fetch_thread (works even if not accepted)
  const data = await fetchThreadData(id);
  if(!data) return;
  // render content similar to openThread but show accept call-to-action
  currentThreadId = null; // not accepted yet
  dom('#chatTitle').textContent = data.inquiry.firstname + ' ' + data.inquiry.lastname + ' (Preview)';
  dom('#chatSubtitle').textContent = data.inquiry.email + ' · ' + (data.inquiry.phone || '');
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
  b.innerHTML = `<div class="author">${escapeHtml(initial.author)}</div>
                 <div class="text">${escapeHtml(initial.message).replace(/\n/g,'<br>')}</div>
                 <div class="time">${formatDate(initial.created_at)}</div>`;
  win.appendChild(b);

  // show an accept CTA
  const ctaWrap = document.createElement('div');
  ctaWrap.style.marginTop = '12px';
  ctaWrap.innerHTML = `<div style="display:flex;gap:8px;align-items:center">
    <button id="previewAccept" class="accept-btn">Accept & Open Conversation</button>
  </div>`;
  win.appendChild(ctaWrap);
  const btn = dom('#previewAccept');
  btn.addEventListener('click', ()=> {
    const incomingBtn = document.querySelector(`#inquiriesList .item[data-id="${id}"] .accept-btn`);
    acceptInquiry(id, incomingBtn || btn);
  });

  dom('#chatFooter').style.display = 'none';
}

// ---------- Send reply ----------
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
      // update local acceptedThreads with returned replies
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

// ---------- Close thread (not necessary per your request) ----------
// If you want a close button, implement it and call currentThreadId = null, hide footer, etc.

// ---------- UI wiring ----------
dom('#searchForm').addEventListener('submit', e => { e.preventDefault(); loadInquiries(); });
dom('#refreshBtn').addEventListener('click', () => { loadInquiries(); });

// ---------- Polling & init ----------
loadInquiries();
pollingTimer = setInterval(loadInquiries, 5000);

// If you want: try to re-open last accepted threads from localStorage (optional)
(function restoreFromSession(){
  try {
    const raw = sessionStorage.getItem('acceptedThreads_v1');
    if(raw){
      const parsed = JSON.parse(raw);
      acceptedThreads = parsed || {};
      renderConversations();
    }
  } catch(e){}
})();

// persist acceptedThreads to sessionStorage on unload (so refresh keeps them)
window.addEventListener('beforeunload', ()=>{
  try {
    sessionStorage.setItem('acceptedThreads_v1', JSON.stringify(acceptedThreads));
  } catch(e){}
});
</script>
</body>
</html>