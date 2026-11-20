const trigger = document.querySelector('.cl-toggle');
const navigation = document.querySelector('#cl-navigation');
const compact = window.matchMedia('(max-width: 760px)');

if (trigger && navigation) {
    const setOpen = (open) => {
        navigation.hidden = compact.matches && !open;
        trigger.setAttribute('aria-expanded', String(compact.matches && open));
    };
    setOpen(false);
    compact.addEventListener('change', () => setOpen(false));
    trigger.addEventListener('click', () => setOpen(navigation.hidden));
    navigation.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && compact.matches) {
            setOpen(false);
            trigger.focus();
        }
    });
}
