const addClickListener = function (elem, name, callback) {
  elem.tabIndex = '0';
  elem.addEventListener('click', () => {
    if (!elem.classList.contains('disabled')) callback();
  });
  elem.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !elem.classList.contains('disabled')) callback();
  });
  elem.classList.add('clickable');
  elem.role = 'button';
  elem['aria-label'] = name;
};
