// assets/js/app.js
document.addEventListener('DOMContentLoaded', () => {

  // === Chart.js - completion history ===
  const ctx = document.getElementById('historyChart');
  let historyChart = null;

  function createChart(values, labels) {
    if (!ctx) return;
    if (historyChart) historyChart.destroy(); // destroy old chart
    historyChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Completed tasks',
          data: values,
          borderRadius: 6,
          borderSkipped: false,
          backgroundColor: '#4e73df'
        }]
      },
      options: {
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
      }
    });
  }

  // initial chart render using PHP-provided data
  if (typeof values !== 'undefined' && typeof labels !== 'undefined') {
    createChart(values, labels);
  }

  // === Task checkbox live update ===
  document.querySelectorAll('.task-checkbox').forEach(cb => {
    cb.addEventListener('change', function() {
      const taskId = this.dataset.id;
      const isChecked = this.checked;
      const taskRow = this.closest('.task-row');

      console.log("🔘 Checkbox changed:", { taskId, isChecked });

      // Show immediate visual feedback
      if (isChecked) {
        taskRow.classList.add('done');
        console.log("✅ Added 'done' class");
      } else {
        taskRow.classList.remove('done');
        console.log("❌ Removed 'done' class");
      }

      // Send update to server
      console.log("📡 Sending request to update_task.php...");
      fetch('update_task.php', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/x-www-form-urlencoded' 
        },
        body: `task_id=${taskId}`
      })
      .then(response => {
        console.log("📥 Response received, status:", response.status);
        if (!response.ok) {
          throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
      })
      .then(data => {
        console.log("📊 Response data:", data);
        
        if (data.error) {
          console.error('❌ Server error:', data.error);
          // Revert checkbox if there was an error
          this.checked = !isChecked;
          if (isChecked) {
            taskRow.classList.remove('done');
          } else {
            taskRow.classList.add('done');
          }
          return;
        }

        // Update chart with returned history and labels
        if (data.history && data.labels) {
          console.log("🔄 Updating chart with new data");
          createChart(data.history, data.labels);
        } else {
          console.log("⚠️ No chart data in response");
        }

        // Update weekly consistency score
        const scoreElem = document.getElementById('consistency-score');
        if (scoreElem) {
          console.log("📈 Updating consistency score to:", data.consistency_score + '%');
          scoreElem.textContent = data.consistency_score + '%';
        } else {
          console.log("⚠️ Consistency score element not found");
        }

        console.log("✅ Update completed successfully");

      })
      .catch(error => {
        console.error('❌ Network error:', error);
        // Revert checkbox on network error
        this.checked = !isChecked;
        if (isChecked) {
          taskRow.classList.remove('done');
        } else {
          taskRow.classList.add('done');
        }
      });
    });
  });

  // === Pomodoro timer (default 25:00) ===
  let duration = 25*60, timer = duration, interval = null;
  const display = document.getElementById('timer-display');
  const startBtn = document.getElementById('start-btn');
  const stopBtn = document.getElementById('stop-btn');
  const resetBtn = document.getElementById('reset-btn');

  function formatTime(t) {
    const m = Math.floor(t/60), s = t%60;
    return `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
  }
  if(display) display.textContent = formatTime(timer);

  startBtn?.addEventListener('click', () => {
    if (interval) return;
    interval = setInterval(() => {
      timer--;
      if(display) display.textContent = formatTime(timer);
      if(timer <= 0) {
        clearInterval(interval); interval=null; timer=duration;
        if(display) display.textContent = formatTime(timer);
        alert('Pomodoro finished! Take a short break.');
      }
    }, 1000);
  });

  stopBtn?.addEventListener('click', () => {
    if(interval){ clearInterval(interval); interval=null; }
  });

  resetBtn?.addEventListener('click', () => {
    if(interval){ clearInterval(interval); interval=null; }
    timer = duration;
    if(display) display.textContent = formatTime(timer);
  });

});