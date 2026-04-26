<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <h1 class="mb-4">Sites WordPress</h1>
        
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-header">Adicionar Novo Site</div>
            <div class="card-body">
                <form method="post">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Nome</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">URL do WordPress</label>
                            <input type="url" name="url" class="form-control" placeholder="https://exemplo.com" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Usuário</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Application Password</label>
                            <input type="password" name="app_password" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Cadastrar Site</button>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">Sites Cadastrados</div>
            <div class="card-body">
                <?php if (empty($websites)): ?>
                    <p class="text-muted">Nenhum site cadastrado.</p>
                <?php else: ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>URL</th>
                                <th>Usuário</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($websites as $site): ?>
                                <tr>
                                    <td><?= $site['name'] ?></td>
                                    <td><?= $site['url'] ?></td>
                                    <td><?= $site['username'] ?></td>
                                    <td>
                                        <form method="post" action="<?= base_url('/websites/' . $site['id']) ?>" class="d-inline">
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Remover este site?')">Remover</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>