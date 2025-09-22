// Make the ghost's eyes follow the cursor

document.addEventListener('mousemove', function(e) {
  const eyes = document.querySelectorAll('.box__ghost-eyes div');
  const box = document.querySelector('.box__ghost-container');
  if (!box) return;

  // Get the center of the ghost container
  const boxRect = box.getBoundingClientRect();
  const centerX = boxRect.left + boxRect.width / 2;
  const centerY = boxRect.top + boxRect.height / 2;

  // Calculate the angle from the center to the mouse
  const dx = e.clientX - centerX;
  const dy = e.clientY - centerY;
  const angle = Math.atan2(dy, dx);

  // Move eyes (max 8px from center)
  const radius = 8;
  const x = Math.cos(angle) * radius;
  const y = Math.sin(angle) * radius;

  eyes.forEach(eye => {
    eye.style.transform = `translate(${x}px, ${y}px)`;
  });
});