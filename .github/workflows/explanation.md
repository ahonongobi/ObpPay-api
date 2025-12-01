rm -rf ~/domains/kazihub.net/public_html
ln -s ~/domains/kazihub.net/laravel-project/public ~/domains/kazihub.net/public_html

generate deploy_github ssh key from y local computer.
send the public to hostinger

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

ssh-keygen -t rsa -b 4096 -C "github-deploy" -f github_deploy -N ""

github_deploy → private key → Github secret
github_deploy.pub → public key → Hostinger 

Add the public key to hostinger
ssh -p 65002 u463784191@92.113.28.11
mkdir -p ~/.ssh
chmod 700 ~/.ssh
echo "PASTE_PUBLIC_KEY_HERE" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys

Test it (Should work without password):
ssh -i ~/github_deploy -o IdentitiesOnly=yes -p 65002 u463784191@92.113.28.11

Add private key to GitHub Secrets

SERVER_SSH_KEY → contents of github_deploy

SERVER_HOST → 92.113.28.11

SERVER_USER → u463784191


Steps to install Composer 2 for your user
cd ~
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=$HOME/bin --filename=composer
php -r "unlink('composer-setup.php');"
⚠️ If $HOME/bin doesn’t exist, create it first: 
mkdir -p $HOME/bin

add it to path sp when type composer it use it:
echo 'export PATH="$HOME/bin:$PATH"' >> ~/.bashrc
source ~/.bashrc

verify:
composer --version

run now composer install inside laravel-project



for the pull to work ( in case of ssh error between github and hostinger)
scp -P 65002 ~/.ssh/github_deploy_key user@host:/home/user/.ssh/github_deploy_key

chmod 600 ~/.ssh/github_deploy_key

nano Set SSH config (~/.ssh/config):

Host github.com
  HostName github.com
  User git
  IdentityFile ~/.ssh/github_deploy_key
  IdentitiesOnly yes


ssh -T git@github.com
