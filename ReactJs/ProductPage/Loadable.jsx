/**
 *
 * Asynchronously loads the component for ProductPage
 *
 */

import loadable from '../../utils/loadable';

export default loadable(() => import('./index'));
