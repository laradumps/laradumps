import Pusher from 'pusher-js';

const APP_KEY = process.env.MIX_PUSHER_APP_KEY ?? process.env.VITE_PUSHER_APP_KEY;
const CLUSTER = process.env.MIX_PUSHER_APP_CLUSTER ?? process.env.VITE_PUSHER_APP_CLUSTER;

const pusher = new Pusher(APP_KEY, {
    cluster: CLUSTER,
    useTLS: false,
    disableStats: true,
    forceTLS: false,
});

const channel = pusher.subscribe('laradumps-livewire-channel');

const overlay = document.createElement('div')
overlay.style.backgroundColor = 'rgba(157,193,229,0.35)'
overlay.style.position = 'fixed'
overlay.style.display = 'flex'
overlay.style.justifyContent = 'center'
overlay.style.alignItems = 'center'
overlay.style.borderRadius = '2px'

const overlayContent = document.createElement('div')
overlayContent.style.backgroundColor = '#818cf8'
overlayContent.style.fontSize = '14px'
overlayContent.style.padding = '3px'
overlayContent.style.borderRadius = '6px'
overlayContent.style.color = 'white'
overlay.appendChild(overlayContent)

channel.bind('remove-highlight-component', () => {
    if (overlay.parentNode) {
        document.body.removeChild(overlay)
    }
})

channel.bind('highlight-component', (data) => {
    const id = data.id
    const component = data.component

    const findLivewire      = window.Livewire.find(id)

    if(findLivewire != null) {
        let element = findLivewire.__instance.el

        if(element != null) {
            element = element.getBoundingClientRect();
            overlay.style.width = ~~element.width + 'px'
            overlay.style.height = ~~element.height + 'px'
            overlay.style.top = ~~element.top + 'px'
            overlay.style.left = ~~element.left + 'px'

            overlayContent.innerHTML = `<span style="opacity: .6;">&lt;</span>${component}<span style="opacity: .6;">&gt;</span>`

            if (overlay.parentNode) {
                document.body.removeChild(overlay)
            }

            document.body.appendChild(overlay)
        }
    }
});
