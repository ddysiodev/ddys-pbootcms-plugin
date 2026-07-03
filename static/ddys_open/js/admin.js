(function () {
  function byId(id) {
    return document.getElementById(id);
  }

  function attr(name, value, quote) {
    if (!value) {
      return '';
    }
    if (quote) {
      return ' ' + name + '="' + String(value).replace(/"/g, '&quot;') + '"';
    }
    return ' ' + name + '=' + String(value).replace(/\s/g, '');
  }

  function build() {
    var kind = byId('ddys-pbootcms-shortcode-kind');
    if (!kind) {
      return;
    }
    var tag = kind.value || 'ddys_latest';
    var slug = byId('ddys-pbootcms-shortcode-slug').value.trim();
    var id = byId('ddys-pbootcms-shortcode-id').value.trim();
    var username = byId('ddys-pbootcms-shortcode-username').value.trim();
    var q = byId('ddys-pbootcms-shortcode-q').value.trim();
    var limit = byId('ddys-pbootcms-shortcode-limit').value.trim();
    var perPage = byId('ddys-pbootcms-shortcode-per-page').value.trim();
    var year = byId('ddys-pbootcms-shortcode-year').value.trim();
    var month = byId('ddys-pbootcms-shortcode-month').value.trim();
    var type = byId('ddys-pbootcms-shortcode-type').value.trim();
    var pairs = [
      ['slug', slug],
      ['id', id],
      ['username', username],
      ['q', q],
      ['limit', (tag === 'ddys_latest' || tag === 'ddys_hot' || tag === 'ddys_suggest') ? (limit || '12') : ''],
      ['per_page', perPage],
      ['year', year],
      ['month', month],
      ['type', type]
    ];
    var shortcode = '[' + tag;
    var label = '{ddys:' + tag.replace(/^ddys_/, '').replace('request_form', 'requestform');
    pairs.forEach(function (pair) {
      shortcode += attr(pair[0], pair[1], true);
      label += attr(pair[0], pair[1], false);
    });
    shortcode += ']';
    label += '}';
    var shortcodeOutput = byId('ddys-pbootcms-shortcode-output');
    var labelOutput = byId('ddys-pbootcms-label-output');
    if (shortcodeOutput) {
      shortcodeOutput.value = shortcode;
    }
    if (labelOutput) {
      labelOutput.value = label;
    }
  }

  var button = byId('ddys-pbootcms-shortcode-build');
  if (button) {
    button.addEventListener('click', build);
    build();
  }
}());

