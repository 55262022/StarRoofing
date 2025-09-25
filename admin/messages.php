<?php
include '../includes/auth.php';
require_once '../database/starroofing_db.php';

$sql = "SELECT id, firstname, lastname, email, message, created_at 
        FROM contact_messages 
        WHERE is_archived = 0 
        ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Messages - Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../css/admin.css"> <!-- keep your existing styling -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
      /* Base styles and reset */
      * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
      }
      body {
          font-family: 'Montserrat', sans-serif;
          line-height: 1.6;
          color: #333;
          background-color: #f5f7f9;
          min-height: 100vh;
          display: flex;
          flex-direction: column;
      }
      .dashboard-container {
          display: flex;
          min-height: 100vh;
      }
      .main-content {
          flex: 1;
          display: flex;
          flex-direction: column;
      }
      .dashboard-content {
          flex: 1;
          padding: 1.5rem;
          overflow-y: auto;
      }
      .page-title {
          font-size: 1.8rem;
          color: #1a365d;
          margin-bottom: 1.5rem;
          font-weight: 700;
      }
      .container {
          padding: 20px;
      }
      h1 {
          text-align: center;
          margin-bottom: 20px;
      }
      .table-actions {
          margin-bottom: 15px;
          display: flex;
          gap: 10px;
          align-items: center;
      }
      table tr.clickable-row { cursor: pointer; }
      table {
          width: 100%;
          border-collapse: collapse;
          background: white;
          box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      }
      th, td {
          padding: 12px;
          border-bottom: 1px solid #ddd;
          text-align: left;
      }
      th {
          background: #1a365d;
          color: white;
      }
      tr:hover {
          background: #f1f1f1;
      }
      .no-data {
          text-align: center;
          padding: 20px;
          background: white;
          border: 1px solid #ddd;
      }
      .btn-archive {
          padding: 6px 12px;
          background: #1a365d;
          color: white;
          border: none;
          cursor: pointer;
          border-radius: 4px;
      }
      .btn-archive:hover {
          background: #e9b949;
          color: #1a365d;
      }
      /* Modal styles */
      .modal {
          display: none;
          position: fixed;
          z-index: 9999;
          padding-top: 100px;
          left: 0; top: 0;
          width: 100%; height: 100%;
          background-color: rgba(0,0,0,0.6);
      }
      .modal-content {
          background: #fff;
          margin: auto;
          padding: 20px;
          border-radius: 10px;
          width: 60%;
          max-width: 700px;
          box-shadow: 0 5px 15px rgba(0,0,0,0.3);
          position: relative;
      }
      .modal-header {
          font-size: 18px;
          font-weight: bold;
          margin-bottom: 10px;
          color: #1a365d;
      }
      .modal-body {
          font-size: 14px;
          line-height: 1.6;
          color: #333;
          white-space: pre-wrap; /* preserve formatting */
      }
      .close-btn {
          position: absolute;
          top: 10px;
          right: 15px;
          font-size: 22px;
          cursor: pointer;
          color: #333;
      }
      .close-btn:hover {
          color: red;
      }
      .clickable {
          color: #1a365d;
          cursor: pointer;
          text-decoration: underline;
      }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <?php include '../includes/admin_sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
      <!-- Top Navigation -->
      <?php include '../includes/admin_navbar.php'; ?>

      <div class="container">
        <h1>Contact Messages</h1>

        <?php if ($result && $result->num_rows > 0): ?>
          <!-- Action Bar -->
          <div class="table-actions">
            <label><input type="checkbox" id="selectAll"> Select All</label>
            <button class="btn-archive" id="archiveBtn"><i class="fa fa-archive"></i> Archive</button>
          </div>

          <!-- Table -->
          <form id="messagesForm">
            <table>
              <thead>
                <tr>
                  <th>Select</th>
                  <th>Firstname</th>
                  <th>Lastname</th>
                  <th>Email</th>
                  <th>Message</th>
                  <th>Date Received</th>
                </tr>
              </thead>
             <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                  <tr class="clickable-row" 
                      data-name="<?= htmlspecialchars($row['firstname'].' '.$row['lastname']) ?>"
                      data-email="<?= htmlspecialchars($row['email']) ?>"
                      data-message="<?= htmlspecialchars($row['message']) ?>"
                      data-date="<?= $row['created_at'] ?>">
                    <td><input type="checkbox" name="ids[]" value="<?= $row['id'] ?>" onclick="event.stopPropagation();"></td>
                    <td><?= htmlspecialchars($row['firstname']) ?></td>
                    <td><?= htmlspecialchars($row['lastname']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= strlen($row['message']) > 40 ? substr(htmlspecialchars($row['message']), 0, 40) . '...' : htmlspecialchars($row['message']) ?></td>
                    <td><?= $row['created_at'] ?></td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </form>
        <?php else: ?>
          <div class="no-data">No messages received yet.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Modal -->
  <div id="messageModal" class="modal">
      <div class="modal-content">
          <span class="close-btn" onclick="closeMessageModal()">&times;</span>
          <div class="modal-header" id="modalTitle">Message</div>
          <div class="modal-body" id="modalBody"></div>
      </div>
  </div>

  <script>
      // Open modal with row data
      document.querySelectorAll(".clickable-row").forEach(row => {
          row.addEventListener("click", function() {
              const name = this.dataset.name;
              const email = this.dataset.email;
              const message = this.dataset.message;
              const date = this.dataset.date;
              openMessageModal(name, email, message, date);
          });
      });

      function openMessageModal(name, email, message, date) {
          document.getElementById('modalTitle').innerHTML = name + " (" + email + ") - " + date;
          document.getElementById('modalBody').innerText = message;
          document.getElementById('messageModal').style.display = "block";
      }

      function closeMessageModal() {
          document.getElementById('messageModal').style.display = "none";
      }

      window.onclick = function(event) {
          const modal = document.getElementById('messageModal');
          if (event.target == modal) {
              modal.style.display = "none";
          }
      }
  </script>

  <script>
    // Select All toggle
    document.getElementById("selectAll").addEventListener("change", function() {
      let checkboxes = document.querySelectorAll('input[name="ids[]"]');
      checkboxes.forEach(cb => cb.checked = this.checked);
    });

    // Archive Button
    document.getElementById("archiveBtn").addEventListener("click", function() {
      let form = document.getElementById("messagesForm");
      let formData = new FormData(form);

      if (!formData.has("ids[]")) {
        Swal.fire("No Selection", "Please select at least one message.", "warning");
        return;
      }

      Swal.fire({
        title: "Are you sure?",
        text: "Selected messages will be archived.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, archive",
      }).then((result) => {
        if (result.isConfirmed) {
          fetch("archive_messages.php", {
            method: "POST",
            body: formData
          })
          .then(res => res.text())
          .then(data => {
            Swal.fire("Archived!", data, "success").then(() => {
              location.reload();
            });
          })
          .catch(err => {
            Swal.fire("Error", "Something went wrong.", "error");
          });
        }
      });
    });
  </script>
</body>
</html>
<?php $conn->close(); ?>
