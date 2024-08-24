git config credential.helper store
git add .
git commit -m $1
# git pull --no-ff
git pull
git push
