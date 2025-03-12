# Guía de PHP_CodeSniffer

## Introducción

PHP_CodeSniffer es una herramienta que detecta violaciones de un estándar de codificación definido y puede corregir automáticamente muchas de estas violaciones. Esta guía explica cómo está configurado en este proyecto y cómo extenderlo.

## Instalación

PHP_CodeSniffer ya está incluido como dependencia de desarrollo en este proyecto. Después de ejecutar `composer install`, estará disponible en `vendor/bin/phpcs` y `vendor/bin/phpcbf`.

## Estándares incluidos

Los estándares incluidos por defecto en PHP_CodeSniffer son:

- PSR1
- PSR2
- PSR12
- PEAR
- Squiz
- Zend
- MySource
- Generic

## Estándares de terceros

Para utilizar estándares de terceros como Slevomat Coding Standard, debes:

1. Instalar la dependencia:
   ```bash
   composer require --dev slevomat/coding-standard
   ```

2. Configurar PHP_CodeSniffer para que encuentre los estándares instalados:
   ```bash
   vendor/bin/phpcs --config-set installed_paths vendor/slevomat/coding-standard
   ```

3. Verificar que el estándar esté disponible:
   ```bash
   vendor/bin/phpcs -i
   ```

## Uso de Slevomat Coding Standard

Si has instalado Slevomat Coding Standard, puedes añadir sus reglas a `phpcs.xml`. Aquí hay algunos ejemplos útiles:

```xml
<!-- Reglas para namespaces -->
<rule ref="SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly"/>
<rule ref="SlevomatCodingStandard.Namespaces.UnusedUses"/>
<rule ref="SlevomatCodingStandard.Namespaces.AlphabeticallySortedUses"/>

<!-- Reglas para clases -->
<rule ref="SlevomatCodingStandard.Classes.ForbiddenPublicProperty"/>
<rule ref="SlevomatCodingStandard.Classes.ModernClassNameReference"/>

<!-- Reglas para excepciones -->
<rule ref="SlevomatCodingStandard.Exceptions.ReferenceThrowableOnly"/>

<!-- Reglas para tipos -->
<rule ref="SlevomatCodingStandard.TypeHints.ParameterTypeHint"/>
<rule ref="SlevomatCodingStandard.TypeHints.PropertyTypeHint"/>
<rule ref="SlevomatCodingStandard.TypeHints.ReturnTypeHint"/>
```

## Creación de sniffs personalizados

Si necesitas crear reglas específicas para tu proyecto, puedes crear sniffs personalizados:

1. Crea un directorio para tu estándar:
   ```bash
   mkdir -p GWMStandard/Sniffs/DDD
   ```

2. Crea un archivo de sniff:
   ```php
   <?php
   // GWMStandard/Sniffs/DDD/ValueObjectImmutabilitySniff.php
   
   namespace GWMStandard\Sniffs\DDD;
   
   use PHP_CodeSniffer\Sniffs\Sniff;
   use PHP_CodeSniffer\Files\File;
   
   class ValueObjectImmutabilitySniff implements Sniff
   {
       public function register()
       {
           return [T_CLASS];
       }
   
       public function process(File $phpcsFile, $stackPtr)
       {
           // Implementación del sniff
       }
   }
   ```

3. Registra tu estándar en `phpcs.xml`:
   ```xml
   <rule ref="GWMStandard.DDD.ValueObjectImmutability"/>
   ```

4. Configura PHP_CodeSniffer para encontrar tu estándar:
   ```bash
   vendor/bin/phpcs --config-set installed_paths /ruta/absoluta/a/GWMStandard
   ```

## Solución de problemas comunes

### Error: Referenced sniff does not exist

Este error ocurre cuando PHP_CodeSniffer no puede encontrar un sniff referenciado en `phpcs.xml`. Causas comunes:

1. **El sniff no está instalado**: Asegúrate de haber instalado la dependencia correspondiente.
2. **PHP_CodeSniffer no conoce la ruta**: Configura `installed_paths` correctamente.
3. **Error en el nombre del sniff**: Verifica que el nombre del sniff sea correcto.

Para verificar los estándares disponibles:
```bash
vendor/bin/phpcs -i
```

Para verificar la configuración actual:
```bash
vendor/bin/phpcs --config-show
```

### Ignorar archivos o directorios

Puedes excluir archivos o directorios en `phpcs.xml`:
```xml
<exclude-pattern>*/vendor/*</exclude-pattern>
<exclude-pattern>*/tests/Fixtures/*</exclude-pattern>
```

### Ignorar reglas específicas

Puedes excluir reglas específicas:
```xml
<rule ref="PSR12">
    <exclude name="PSR12.Files.FileHeader"/>
</rule>
```

## Recursos adicionales

- [Documentación oficial de PHP_CodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer/wiki)
- [Slevomat Coding Standard](https://github.com/slevomat/coding-standard)
- [PSR-12: Extended Coding Style](https://www.php-fig.org/psr/psr-12/) 
