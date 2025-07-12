//-----------------------------------------------------------
// Lib
//-----------------------------------------------------------
import { Fancybox } from '@fancyapps/ui';

Fancybox.bind('[data-fancybox]', {
  closeExisting: true,
  fadeEffect: false,
  zoomEffect: false,
  showClass: false,
  hideClass: false,
  dragToClose: false,

  Carousel: {
    transition: false,
  },
});

//-----------------------------------------------------------
// Kickstart
//-----------------------------------------------------------

//-----------------------------------------------------------
// BASE
//-----------------------------------------------------------

//-----------------------------------------------------------
// Components
//-----------------------------------------------------------

import './003-components/common/api.js';
import './003-components/common/select.js';
import './003-components/common/checkbox.js';
import './003-components/common/input.js';
import './003-components/common/notification.js';
import './003-components/front/catalog.js';
import './003-components/front/item.js';
import './003-components/front/security.js';
import './003-components/front/header.js';
import './003-components/front/cart.js';

//-----------------------------------------------------------
// SECTIONS
//-----------------------------------------------------------

