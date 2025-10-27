#!/bin/bash

# 📁 Script de Validation Organisation Documentation
# RestroSaaS - Vérification structure projet

echo "📁 VALIDATION ORGANISATION DOCUMENTATION"
echo "========================================"

# Vérifier répertoire racine
echo "📋 Vérification répertoire racine..."
root_md_count=$(find . -maxdepth 1 -name "*.md" -type f | wc -l)
echo "Fichiers .md dans racine: $root_md_count"

if [ $root_md_count -eq 1 ]; then
    echo "✅ Organisation correcte (seul README.md en racine)"
else
    echo "⚠️ Il devrait y avoir seulement README.md en racine"
fi

# Vérifier dossier documentation
echo ""
echo "📚 Vérification dossier documentation..."
if [ -d "documentation" ]; then
    doc_md_count=$(find documentation/ -name "*.md" -type f | wc -l)
    echo "✅ Dossier documentation existe"
    echo "📄 Fichiers .md dans documentation: $doc_md_count"
    
    # Vérifier fichiers clés
    key_files=(
        "INDEX_DOCUMENTATION_COMPLETE.md"
        "FINAL_ADDONS_REPORT.md"
        "README-FINAL-SUCCESS.md"
        "DEPLOYMENT_GUIDE_PRODUCTION.md"
        "BUG_FIX_P1013_REPORT.md"
        "BUG_FIX_P1009_QR_DESIGN_REPORT.md"
        "NETWORK_WARNING_768_ANALYSIS.md"
    )
    
    echo ""
    echo "🔍 Vérification fichiers clés déplacés..."
    for file in "${key_files[@]}"; do
        if [ -f "documentation/$file" ]; then
            echo "✅ $file"
        else
            echo "❌ $file manquant"
        fi
    done
else
    echo "❌ Dossier documentation manquant"
fi

# Vérifier README principal
echo ""
echo "📖 Vérification README principal..."
if [ -f "README.md" ]; then
    echo "✅ README.md existe en racine"
    
    # Vérifier contenu README
    if grep -q "documentation/" README.md; then
        echo "✅ README référence le dossier documentation"
    else
        echo "⚠️ README ne référence pas le dossier documentation"
    fi
else
    echo "❌ README.md manquant en racine"
fi

# Statistiques finales
echo ""
echo "📊 STATISTIQUES ORGANISATION"
echo "============================"
echo "📁 Fichiers .md racine: $root_md_count (attendu: 1)"
echo "📚 Fichiers .md documentation: $doc_md_count"
echo "📄 Total fichiers documentation: $((root_md_count + doc_md_count))"

echo ""
echo "🎯 STRUCTURE PROJET"
echo "==================="
echo "/"
echo "├── README.md (principal)"
echo "├── documentation/"
echo "│   ├── INDEX_DOCUMENTATION_COMPLETE.md"
echo "│   ├── FINAL_ADDONS_REPORT.md"
echo "│   ├── README-FINAL-SUCCESS.md"
echo "│   └── ... (90 fichiers documentation)"
echo "├── app/"
echo "├── addons/"
echo "└── ..."

echo ""
if [ $root_md_count -eq 1 ] && [ -d "documentation" ] && [ -f "README.md" ]; then
    echo "✅ ORGANISATION PARFAITE!"
    echo "🎉 Documentation bien organisée et accessible"
else
    echo "⚠️ Améliorations possibles dans l'organisation"
fi

echo ""
echo "📚 Pour accéder à la documentation complète:"
echo "👉 Consultez: documentation/INDEX_DOCUMENTATION_COMPLETE.md"