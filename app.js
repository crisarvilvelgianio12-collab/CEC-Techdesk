// basic client-side search for tickets on pages that include #search
document.addEventListener('input', (e)=>{
  if (e.target.id === 'search') {
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('.tickets li').forEach(li=>{
      li.style.display = li.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  }
});
