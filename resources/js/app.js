import './bootstrap';

import Alpine from 'alpinejs';
import { appBasePath, withBasePath } from './base-path';

window.Alpine = Alpine;
window.appBasePath = appBasePath;
window.withBasePath = withBasePath;

Alpine.start();
