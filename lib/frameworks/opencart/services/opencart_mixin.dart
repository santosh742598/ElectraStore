import '../../../services/service_config.dart';
import '../index.dart';
import 'opencart.dart';

mixin OpencartMixin on ConfigMixin {
  configOpencart(appConfig) {
    OpencartApi().setAppConfig(appConfig);
    serviceApi = OpencartApi();
    widget = OpencartWidget();
  }
}
