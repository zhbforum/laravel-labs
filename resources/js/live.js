import './bootstrap';

window.addEventListener('load', () => {
    const projectId = 10;

    const log = (msg) => {
        const el = document.getElementById('log');
        if (!el) return;
        el.innerHTML += `<p>${msg}</p>`;
    };

    console.log('window.Echo =', window.Echo);

    window.Echo.private(`project.${projectId}`)
        .subscribed(() => {
            console.log('subs to project.' + projectId);
            log('✅ Підписалися на project.' + projectId);
        })
        .error((error) => {
            console.error('chanell error', error);
            log('❌ Помилка каналу, дивись консоль');
        })
        .listen('.task.updated', (e) => {
            console.log('task event', e);
            log(`🟡 Задача "${e.title}" змінена (${e.status})`);
        })
        .listen('.comment.created', (e) => {
            console.log('comment event', e);
            log(`💬 Новий коментар до задачі #${e.task_id}: ${e.body} (автор: ${e.author ?? 'невідомий'})`);
        });
});