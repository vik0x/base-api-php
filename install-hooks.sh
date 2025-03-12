#!/bin/bash

# Asegurarse de que el script se ejecuta desde la raíz del proyecto
if [ ! -f "composer.json" ]; then
    echo "Este script debe ejecutarse desde la raíz del proyecto"
    exit 1
fi

# Verificar si git está inicializado
if [ ! -d ".git" ]; then
    echo "Inicializando repositorio git..."
    git init
fi

# Instalar composer-git-hooks
echo "Instalando composer-git-hooks..."
composer require --dev brainmaestro/composer-git-hooks

# Instalar los hooks
echo "Instalando git hooks..."
vendor/bin/cghooks add --no-lock

echo "Hooks instalados correctamente!"
echo "Los siguientes hooks están configurados:"
echo "- pre-commit: Ejecuta verificaciones de estilo y tests unitarios"
echo "- pre-push: Ejecuta todas las verificaciones y tests"

# Hacer el script ejecutable
chmod +x install-hooks.sh 
