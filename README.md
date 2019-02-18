# Das Moodle Plugin „Level“ – ein Moodle-Werkzeug für einen leichten Einstieg in Moodle (für Trainer!)

## Ziel des Level Plugins ist es ein Moodle-Werkzeug zu schaffen, das u.a.
* den Funktionsumfang für Trainer zu minimieren und damit den Einstieg zu erleichtern

**Voraussetzung:**
* Moodle 3.5+                             

## Installation:

Bei der Konfiguration des Level Plugins sind die Level (Einsteiger, Erfahrener Nutzer und Experten) durch einen Admin einzustellen.
Das benutzerdefinierte Level kann jeder Nutzer über sein Profil individuell einstellen.

## Technische Dokumentation:
### Ordnerstruktur
* local/authoringcapability
* theme/boost_level
* user/field/authoringlevelmenu

**Einbau in ein anderes Theme**
In der config.php muss zunächst folgende Zeile enthalten sein.

$THEME->rendererfactory = 'theme_overridden_renderer_factory';

Im classes Ordner (falls noch nicht vorhanden, muss dieser angelegt werden)

Dort müssen die PHP Dateien core_course_renderer und core_renderer angelegt bzw. ergänzt werden.

### Für das Level Plugin sind folgende Methoden relevant

* classes/output/core/course_renderer.php
  * public function course_modchooser
* classes/output/core_renderer.php
  * public function header
  * protected function block_content
* classes/output/block_settings_renderer.php
  * public function settings_tree
