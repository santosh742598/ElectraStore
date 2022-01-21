import '../../../services/service_config.dart';
import '../index.dart';
import 'magento.dart';

mixin MagentoMixin on ConfigMixin {
  configMagento(appConfig) {
    MagentoApi().setAppConfig(appConfig);
    serviceApi = MagentoApi();
    widget = MagentoWidget();
  }
}
