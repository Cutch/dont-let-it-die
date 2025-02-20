const addClickListener = function (elem, name, callback) {
  elem.tabIndex = '0';
  const click = (e) => {
    if (!elem.classList.contains('disabled')) {
      callback(e);
      e.preventDefault();
    }
  };
  elem.addEventListener('click', click);
  const keydown = (e) => {
    if (e.key === 'Enter' && !elem.classList.contains('disabled')) {
      callback(e);
      e.preventDefault();
    }
  };
  elem.addEventListener('keydown', keydown);
  elem.classList.add('clickable');
  elem.role = 'button';
  elem['aria-label'] = name;
  return () => {
    elem.removeEventListener('click', click);
    elem.removeEventListener('keydown', keydown);
  };
};
