# GWM Backend

## Configuración del entorno de desarrollo

### Requisitos previos
- PHP 8.2 o superior
- Composer
- Git

### Instalación

1. Clona el repositorio
```bash
git clone [url-del-repositorio]
cd back
```

2. Instala las dependencias
```bash
composer install
```

3. Instala los git hooks
```bash
# Opción 1: Usando el script de instalación
./install-hooks.sh

# Opción 2: Manualmente
vendor/bin/cghooks add --no-lock
```

## Git Hooks

Este proyecto utiliza git hooks para garantizar la calidad del código antes de cada commit y push:

### Pre-commit Hook

El hook pre-commit se ejecuta automáticamente antes de cada commit y realiza las siguientes verificaciones:

- Verifica el estilo de código con PHP_CodeSniffer
- Analiza el código con PHPStan
- Ejecuta las pruebas unitarias

Si alguna de estas verificaciones falla, el commit será rechazado.

### Pre-push Hook

El hook pre-push se ejecuta antes de cada push y realiza todas las verificaciones:

- Verifica el estilo de código
- Analiza el código
- Ejecuta todas las pruebas

### Saltarse los hooks (no recomendado)

En casos excepcionales, puedes saltarte los hooks usando la opción `--no-verify`:

```bash
git commit -m "Mensaje del commit" --no-verify
git push --no-verify
```

**Nota**: Esto debe usarse solo en situaciones excepcionales, ya que los hooks están diseñados para mantener la calidad del código.

## Estándares de código y calidad

Este proyecto utiliza varias herramientas para garantizar la calidad del código y el cumplimiento de los estándares:

### PHP_CodeSniffer

Verifica que el código siga los estándares de codificación definidos en `phpcs.xml`.

```bash
# Verificar el código
composer phpcs

# Corregir automáticamente problemas
composer phpcbf
```

### PHP-CS-Fixer

Complementa a PHP_CodeSniffer con correcciones automáticas adicionales.

```bash
# Corregir el formato del código
composer format
```

### PHPStan

Realiza análisis estático del código para detectar posibles errores.

```bash
# Analizar el código
composer analyse
```

### Comandos combinados

```bash
# Verificar estilo (phpcs + phpstan)
composer check-style

# Corregir estilo (phpcbf + php-cs-fixer)
composer fix-style

# Verificar todo (estilo + tests)
composer check-all

# Verificación pre-commit
composer pre-commit
```

### Configuración de IDE

Para una mejor experiencia de desarrollo, configura tu IDE para que utilice estas herramientas:

#### Visual Studio Code
1. Instala las extensiones:
   - "PHP Sniffer & Beautifier"
   - "PHP Intelephense"
   - "PHP CS Fixer"
   - "PHPStan"
2. Configura las extensiones para usar los archivos de configuración del proyecto

#### PhpStorm
1. Ve a Settings > Editor > Inspections > PHP > Quality Tools
2. Configura PHP_CodeSniffer, PHP-CS-Fixer y PHPStan
3. Establece las rutas a los ejecutables y archivos de configuración

## Estructura del proyecto

El proyecto sigue una arquitectura basada en Domain-Driven Design (DDD):

```
src/
├── Application/    # Casos de uso y servicios de aplicación
├── Domain/         # Entidades, value objects, repositorios (interfaces)
├── Infrastructure/ # Implementaciones concretas, adaptadores, etc.
└── UI/             # Controladores, CLI, etc.
```

## Convenciones de código específicas para DDD

### Domain Layer
- Las entidades deben ser inmutables cuando sea posible
- Los Value Objects deben ser siempre inmutables
- Usar DTOs para transferir datos entre capas
- Las interfaces de repositorios deben estar en el dominio
- Los servicios de dominio deben ser stateless

### Application Layer
- Los casos de uso deben seguir el principio de responsabilidad única
- Usar Command/Query Separation cuando sea apropiado
- Implementar validación de entrada en esta capa

### Infrastructure Layer
- Implementaciones concretas de interfaces definidas en el dominio
- Adaptadores para servicios externos
- Configuración de frameworks y bibliotecas

### UI Layer
- Controladores delgados que solo coordinan flujos
- Transformadores de datos para presentación
- Manejo de errores HTTP

## Pruebas

Para ejecutar las pruebas:

```bash
composer test

# Con cobertura
composer test-coverage
``` 
