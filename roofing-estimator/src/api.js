export async function getEstimation({ length, width, height, material }) {
  const form = new URLSearchParams({ length, width, height, material });
  const res = await fetch('http://localhost/starroofing/admin/3D_model.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: form.toString()
  });
  return res.json();
}